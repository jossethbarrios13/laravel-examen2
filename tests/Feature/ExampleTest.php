<?php

namespace Tests\Feature;

use App\Models\Lista;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            ->assertSee('https://wa.me/?text='.rawurlencode('Te comparto mi lista: '.$url), false);
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
