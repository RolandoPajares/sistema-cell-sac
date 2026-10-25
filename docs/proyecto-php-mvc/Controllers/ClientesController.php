<?php

require_once "./Models/UsuariosModel.php";

class ClientesController extends Controller
{
    public function index()
    {
        $this->listar();
    }

    public function listar()
    {

        $data = $this->model->select("cliente.*, U.email")
            // ->join("usuario U", "U.id = cliente.id_usuario")
            ->where(["cliente.estado = 1"])
            ->get();
        $this->views->render($this, "listar", $data);
    }

    public function ver($id)
    {
        $cliente = $this->model->select("cliente.*, U.email, U.usuario")
            ->where(["cliente.id = $id"])
            ->first();
        $this->views->render($this, "ver", $cliente);
    }

    public function registrar()
    {
        $this->views->render($this, "formulario");
    }

        public function editar($id)
    {
        $cliente = $this->model->select("cliente.*, U.email, U.usuario")
            ->where(["cliente.id = $id"])
            ->first();
        // debug($cliente);
        $this->views->render($this, "form_edit", $cliente);
    }

    public function eliminar($id)
    {
        try {
            if (!$id) throw new Exception("ID no encontrado");

            $rsptaCli = $this->model->update($id, [
                "estado" => 0,
            
            ]);

            if (!$rsptaCli) throw new Exception("No se pudo desactivar Cliente");

            header("Location: " . BASE_URL . "/clientes/listar?e=success");
        } catch (\Exception $e) {
            header("Location: " . BASE_URL . "/clientes/listar?e=" . $e->getMessage());
        }
    }


    public function guardar()
    {
        try {
            if (!$_POST || empty($_POST)) throw new Exception("No hay datos a procesar");
            if (!$this->esFormValido($_POST)) return;

            $usuarioModel = new UsuariosModel();

            $idUsu = $usuarioModel->insert([
                "email" => $_POST["correo"],
                "usuario" => $_POST["usuario"],
                "clave" => md5($_POST["clave"])
            ]);

            if (!$idUsu) throw new Exception("No se pudo registrar Usuario");

            $idCli = $this->model->insert([
                "apellidos" => $_POST["apellidos"],
                "nombres" => $_POST["nombres"],
                "direccion" => $_POST["direccion"],
                "telefono" => $_POST["telefono"],
                "dni" => $_POST["dni"],
                "id_usuario" => $idUsu,
            ]);

            if (!$idCli) throw new Exception("No se pudo registrar Cliente");

            header("Location: " . BASE_URL . "/clientes/listar?e=success");
        } catch (\Exception $e) {
            header("Location: " . BASE_URL . "/clientes/listar?e=" . $e->getMessage());
        }
    }

 public function guardar_edit()
    {
        try {
            if (!$_POST || empty($_POST)) throw new Exception("No hay datos a procesar");
            if (!$_POST["id_cliente"] || empty($_POST["id_cliente"])) throw new Exception("ID no encontrado");
            if (!$this->esFormValido($_POST)) return;

            $usuarioModel = new UsuariosModel();

            $paramsUsu = [
                "email" => $_POST["correo"],
                "usuario" => $_POST["usuario"],
            ];
            if ($_POST["clave"])  $paramsUsu["clave"]  = md5($_POST["clave"]);
            $rsptaUsu = $usuarioModel->update($_POST["id_usuario"], $paramsUsu);

            if (!$rsptaUsu) throw new Exception("Error al actualizar usuario.");

            $rsptaCli = $this->model->update($_POST["id_cliente"], [
                "apellidos" => $_POST["apellidos"],
                "nombres" => $_POST["nombres"],
                "direccion" => $_POST["direccion"],
                "telefono" => $_POST["telefono"],
                "dni" => $_POST["dni"],
            ]);

            if (!$rsptaCli) throw new Exception("No se pudo actualizar Cliente");

            header("Location: " . BASE_URL . "/clientes/listar?e=success");
        } catch (\Exception $e) {
            header("Location: " . BASE_URL . "/clientes/listar?e=" . $e->getMessage());
        }
    }


    private function esFormValido($formData)
    {
        return true;
    }
}
