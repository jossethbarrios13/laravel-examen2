<?php

namespace Tests\Feature;

use App\Models\Lista;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_la_raiz_redirige_a_mis_listas(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/listas');
    }

    private function prepararLista(): Lista
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('nombre', 150);
            $table->string('email', 190)->unique();
            $table->string('google_id', 191)->nullable()->unique();
        });
        Schema::create('listas', function (Blueprint $table) {
            $table->increments('id_lista');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('titulo', 200);
            $table->string('token', 20)->unique();
        });
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id_item');
            $table->unsignedInteger('id_lista')->nullable();
            $table->string('descripcion', 255);
            $table->boolean('completado')->default(false);
        });

        $usuario = Usuario::create(['nombre' => 'Persona de prueba', 'email' => 'prueba@example.com']);
        $lista = $usuario->listas()->create(['titulo' => 'Lista compartida', 'token' => 'abcdefghijklmnopqrst']);
        $lista->items()->create(['descripcion' => 'Elemento de prueba']);

        return $lista;
    }

    public function test_login_y_listas_no_son_interceptadas_por_la_ruta_publica(): void
    {
        $this->get('/login')->assertOk()->assertSee('Continuar con Google');
        $this->get('/listas')->assertRedirect('/login');
        $this->get('/listas/create')->assertRedirect('/login');
    }

    public function test_socialite_de_la_guia_redirige_a_google(): void
    {
        config([
            'services.google.client_id' => 'cliente-de-prueba',
            'services.google.client_secret' => 'secreto-de-prueba',
            'services.google.redirect' => 'http://localhost:8000/auth/google/callback',
        ]);

        $response = $this->get('/auth/google')->assertRedirect();
        $url = $response->headers->get('Location');
        $this->assertSame('accounts.google.com', parse_url($url, PHP_URL_HOST));
        parse_str(parse_url($url, PHP_URL_QUERY), $parametros);
        $this->assertSame('http://localhost:8000/auth/google/callback', $parametros['redirect_uri']);
        $this->assertNotEmpty($parametros['state']);
    }

    public function test_callback_de_google_registra_e_inicia_sesion(): void
    {
        $this->prepararLista();
        $googleUser = (new GoogleUser())->setRaw(['email_verified' => true])->map([
            'id' => 'google-de-prueba',
            'name' => 'Usuario Google',
            'email' => 'google@example.com',
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get('/auth/google/callback')->assertRedirect(route('listas.index'));
        $usuario = Usuario::where('google_id', 'google-de-prueba')->firstOrFail();
        $this->assertAuthenticatedAs($usuario);
        $this->assertSame('google@example.com', $usuario->email);
    }

    public function test_lista_publica_se_consulta_sin_autenticacion(): void
    {
        $lista = $this->prepararLista();
        $this->get(route('visitantes.show', $lista->token))->assertOk()
            ->assertSee('Lista compartida')->assertSee('Persona de prueba')->assertSee('Elemento de prueba');
        $this->get('/token-inexistente')->assertNotFound();
        $this->assertGuest();
    }

    public function test_index_enlaza_la_vista_publica_y_whatsapp(): void
    {
        $lista = $this->prepararLista();
        $url = route('visitantes.show', $lista->token);
        $this->actingAs($lista->usuario)->get('/listas')->assertOk()
            ->assertSee($url, false)
            ->assertSee('https://wa.me/?text='.urlencode('Mira mi lista "'.$lista->titulo.'": '.$url), false);
    }

    public function test_visitante_marca_y_desmarca_el_item_y_se_actualiza_el_progreso(): void
    {
        $lista = $this->prepararLista();
        $item = $lista->items()->firstOrFail();
        $url = route('visitantes.update', ['token' => $lista->token, 'id_item' => $item->id_item]);

        $this->patchJson($url, ['completado' => true])->assertOk()
            ->assertJson(['completado' => true, 'total' => 1, 'hechos' => 1, 'porcentaje' => 100]);
        $this->assertTrue($item->fresh()->completado);
        $this->patchJson($url, ['completado' => false])->assertOk()
            ->assertJson(['completado' => false, 'hechos' => 0, 'porcentaje' => 0]);
        $this->assertFalse($item->fresh()->completado);
        $this->assertGuest();
    }

    public function test_token_no_permite_actualizar_items_de_otra_lista(): void
    {
        $lista = $this->prepararLista();
        $otra = $lista->usuario->listas()->create(['titulo' => 'Otra', 'token' => '12345678901234567890']);
        $item = $otra->items()->create(['descripcion' => 'Item ajeno']);
        $this->patchJson(route('visitantes.update', ['token' => $lista->token, 'id_item' => $item->id_item]), [
            'completado' => true,
        ])->assertNotFound();
        $this->assertFalse($item->fresh()->completado);
    }

    public function test_edicion_privada_conserva_token_y_estado_publico(): void
    {
        $lista = $this->prepararLista();
        $item = $lista->items()->firstOrFail();
        $item->update(['completado' => true]);

        $this->actingAs($lista->usuario)->put(route('listas.update', $lista), [
            'titulo' => 'Lista editada',
            'items' => [['id_item' => $item->id_item, 'descripcion' => 'Elemento editado']],
        ])->assertRedirect(route('listas.index'));

        $this->assertSame($lista->token, $lista->fresh()->token);
        $this->assertTrue($item->fresh()->completado);
        $this->get(route('visitantes.show', $lista->token))->assertOk()
            ->assertSee('Lista editada')->assertSee('Elemento editado');
    }
}
