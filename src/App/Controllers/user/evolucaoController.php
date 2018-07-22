<?php

namespace App\Controllers\user;

use App\Core\Controller;
use App\Core\Validate;
use App\Model\Evolucao;
use App\Model\Pacientes;
use App\Model\User;

class evolucaoController extends Controller
{
    private $paciente;
    private $evolucao;
    private $user;
    public function __construct()
    {
        $this->user = (new User)->user();
        $this->paciente = new Pacientes;
        $this->evolucao = new Evolucao;
    }

    public function index($request, $response, $args)
    {
        //alem do paciente tenho que encaminhar as evolucao
        $paciente = $this->paciente;
        $user = $this->user;
        $evolucao = $this->evolucao;
        $paciente = $paciente->select()->where('id_paciente', $args['id'])->first();

        $evolucoes = $evolucao->get_evolucoes($paciente->id_paciente, $user->id_user);
        $dados['paciente'] = $paciente;
        $dados['title'] = 'Evolução';
        $dados['evolucoes'] = $evolucoes;
        $dados['links'] = $this->evolucao->links();
        $this->view('user.evolucao', $dados);
    }

    public function create($request, $response, $args)
    {
        //alem do paciente tenho que encaminhar as evolucao
        $args['acao'] = 'Salvar';
        $this->view('user.cadastra_evolucao', $args);
    }

    public function store($request, $response, $args)
    {
        $validate = new Validate;
        $data = $validate->validate([
            'descricao' => 'required',
            'data' => 'required',
        ]);

        if ($validate->hasErrors()) {
            return $this->create();
        }
        /**
         *Terminei a validação, agora estou pegando o id do usuário,
         * tenho que pegar o id do paciente esqueci de mandar por parametro no form.
         */
        $user = $this->user;
        $data->id_user = $user->id_user;
        $evolucao = $this->evolucao->create((array) $data);
        if ($evolucao) {
            $arg = (array) $data;
            $id_paciente = $arg['id_paciente'];
            $this->listEvolucao($id_paciente);
        }
    }

    public function listEvolucao($id_paciente)
    {
        $evolucoes = $this->evolucao->get_evolucoes($id_paciente, $this->user->id_user);
        $dados['links'] = $this->evolucao->links();
        $dados['evolucoes'] = $evolucoes;
        $this->view('user.table_evolucoes', $dados);
    }
    public function destroy($request, $response, $args)
    {

        $deleted = $this->evolucao->find('id_evolucao', $args['id'])->delete();
        if ($deleted) {
            $id_paciente = $args['id_paciente'];
            $this->listEvolucao($id_paciente);
        }
    }

    public function edit($request, $response, $args)
    {

        $evolucao = $this->evolucao;
        $evolucao = $evolucao->select()->where('id_evolucao', $args['id'])->first();
        $this->view('user.cadastra_evolucao', [
            'acao' => 'Editar',
            'evolucao' => $evolucao,
        ]);
    }

    public function update($request, $response, $args)
    {
        $data = $validate->validate([
            'nome' => 'required',
            'documento' => 'required',
            'telefone' => 'required:phone:max@14',
        ]);

        if ($validate->hasErrors()) {
            return back();
        }

        $update = $this->paciente->find('id_paciente', $args['id'])->update((array) $data);

        if ($update) {
            return back();
        }
    }
}
