<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/clientes/agregar_cliente.php";
include "vistas/modulos/modales/clientes/editar_cliente.php";
include "vistas/assets/eventos/clientes.eventos.php";
?>

<div>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCliente">
    Agregar nuevo cliente
  </button>
</div>

<div class="row">
  <div class="col-8">
    <div class="box-body pt-3">
      <table class="table table-bordered table-striped tablas" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>NIT</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $clientes = ControladorClientes::ctrMostrarClientes(null, null);
            foreach ($clientes as $value):
              $id = $value["id_cliente"];
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($value["nombre"]) ?></td>
            <td><?= htmlspecialchars($value["nit"] ?? "—") ?></td>
            <td>
              <button class="btn btn-warning btnEditarCliente"
                data-id="<?= $id ?>"
                data-nombre="<?= htmlspecialchars($value["nombre"]) ?>"
                data-nit="<?= htmlspecialchars($value["nit"] ?? "") ?>"
                data-toggle="modal"
                data-target="#modalEditarCliente">
                Editar
              </button>
              <button class="btn btn-danger btnEliminarCliente" data-id="<?= $id ?>">
                Eliminar
              </button>
              <form id="formEliminarCliente-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarCliente" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>