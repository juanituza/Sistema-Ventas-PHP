<?php

include_once "config.php";
include_once "entidades/cliente.php";
include_once "entidades/producto.php";
include_once "entidades/venta.php";

$venta = new Venta();
$venta->cargarFormulario($_REQUEST);

$pg = "Listado de Ventas";

if ($_POST) {
    if (isset($_POST["btnGuardar"])) {
        if (isset($_GET["id"]) && $_GET["id"] > 0) {
            //Actualizo un cliente existente
            $venta->actualizar();
        } else {
            //Es nuevo
            $venta->insertar();
        }
        $msg["texto"] = "Guardado correctamente";
        $msg["codigo"] = "alert-success";
    } else if (isset($_POST["btnBorrar"])) {
        $venta->eliminar();
        header("Location: venta-listado.php");
    }
}

if (isset($_GET["do"]) && $_GET["do"] == "buscarProducto") {
    $aResultado = array();
    $idProducto = $_GET["id"];
    $producto = new Producto();
    $producto->idproducto = $idProducto;
    $producto->obtenerPorId();
    $aResultado["precio"] = $producto->precio;
    $aResultado["cantidad"] = $producto->cantidad;
    echo json_encode($aResultado);
    exit;
}

if (isset($_GET["id"]) && $_GET["id"] > 0) {
    $venta->obtenerPorId();
}


$producto = new Producto();
$aProductos = $producto->obtenerTodos();

$cliente = new Cliente();
$aClientes = $cliente->obtenerTodos();

include_once("header.php");
?>
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Venta</h1>
    <?php if (isset($msg)) : ?>
        <div class="row">
            <div class="col-12">
                <div class="alert <?php echo $msg["codigo"]; ?>" role="alert">
                    <?php echo $msg["texto"]; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-12 mb-3">
            <a href="venta-listado.php" class="btn btn-primary mr-2">Listado</a>
            <a href="venta-formulario.php" class="btn btn-primary mr-2">Nuevo</a>
            <button type="submit" class="btn btn-success mr-2" id="btnGuardar" name="btnGuardar">Guardar</button>
            <button type="submit" class="btn btn-danger" id="btnBorrar" name="btnBorrar">Borrar</button>
        </div>
    </div>
    <div class="row">
        <div class="col-6 form-group">
            <label for="txtFecha" class="d-block">Fecha:</label>
            <select class="form-control d-inline" name="txtFechaDia" id="txtFechaDia" style="width: 80px">
                <option selected="" disabled="">DD</option>
                <?php for ($i = 1; $i <= 31; $i++) : ?>
                    <?php if ($venta->fecha != "" && $i == date_format(date_create($venta->fecha), "d")) : ?>
                        <option selected><?php echo $i; ?></option>
                    <?php else : ?>
                        <option><?php echo $i; ?></option>
                    <?php endif; ?>
                <?php endfor; ?>
            </select>
            <select class="form-control d-inline" name="txtFechaMes" id="txtFechaMes" style="width: 80px">
                <option selected="" disabled="">MM</option>
                <?php for ($i = 1; $i <= 12; $i++) : ?>
                    <?php if ($venta->fecha != "" && $i == date_format(date_create($venta->fecha), "m")) : ?>
                        <option selected><?php echo $i; ?></option>
                    <?php else : ?>
                        <option><?php echo $i; ?></option>
                    <?php endif; ?>
                <?php endfor; ?>
            </select>
            <select class="form-control d-inline" name="txtFechaAnio" id="txtFechaAnio" style="width: 100px">
                <option selected="" disabled="">YYYY</option>
                <?php for ($i = 1900; $i <= date("Y"); $i++) : ?>
                    <?php if ($venta->fecha != "" && $i == date_format(date_create($venta->fecha), "Y")) : ?>
                        <option selected><?php echo $i; ?></option>
                    <?php else : ?>
                        <option><?php echo $i; ?></option>
                    <?php endif; ?>
                <?php endfor; ?> ?>
            </select>
        </div>
        <div class="col-6 form-group">
            <label for="lstCliente">Cliente:</label>
            <select class="form-control" name="lstCliente" id="lstCliente" onchange="" required>
                <option value="" disabled selected>Seleccionar</option>
                <?php foreach ($aClientes as $cliente) : ?>
                    <?php if ($venta->fk_idcliente == $cliente->idcliente) : ?>
                        <option selected value="<?php echo $cliente->idcliente; ?>"><?php echo $cliente->nombre; ?></option>
                    <?php else : ?>
                        <option value="<?php echo $cliente->idcliente; ?>"><?php echo $cliente->nombre; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 form-group">
            <label for="lstProducto">Producto:</label>
            <select class="form-control" name="lstProducto" id="lstProducto" onchange="fBuscarPrecio();" required>
                <option value="" disabled selected>Seleccionar</option>
                <?php foreach ($aProductos as $producto) : ?>
                    <?php if ($venta->fk_idproducto == $producto->idproducto) : ?>
                        <option selected value="<?php echo $producto->idproducto; ?>"><?php echo $producto->nombre; ?></option>
                    <?php else : ?>
                        <option value="<?php echo $producto->idproducto; ?>"><?php echo $producto->nombre; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 form-group">
            <label for="txtCantidad">Cantidad:</label>
            <input type="number" class="form-control" name="txtCantidad" id="txtCantidad" onchange="fCalcularTotal();" value="<?php echo $venta->cantidad ?>">
            <span id="msgStock" class="text-danger" style="display:none;">No hay stock suficiente</span>
        </div>
        <div class="col-6 form-group">
            <label for="txtPreciounitario">Precio Unitario:</label>
            <input type="text" class="form-control" name="txtPreciounitario" id="txtPrecioUniCurrency" value="$<?php echo $venta->preciounitario ?>">
            <input type="hidden" class="form-control" name="txtPreciounitario" id="txtPreciounitario" value="<?php echo $venta->preciounitario ?>">
        </div>
        <div class="col-6 form-group">
            <label for="txtTotal">Total:</label>
            <input type="number" class="form-control" name="txtTotal" id="txtTotal" value="<?php echo $venta->total ?>">
        </div>

    </div>


</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->
<script>
    $(document).ready(function() {
        var idCliente = '<?php echo isset($cliente) && $cliente->idcliente > 0 ? $cliente->idcliente : 0 ?>';

    });

    function fBuscarLocalidad() {
        idProvincia = $("#lstProvincia option:selected").val();
        $.ajax({
            type: "GET",
            url: "cliente-formulario.php?do=buscarLocalidad",
            data: {
                id: idProvincia
            },
            async: true,
            dataType: "json",
            success: function(respuesta) {
                let resultado = "<option value='0' disabled selected>Seleccionar</option>";
                respuesta.forEach(function(valor, indice) {
                    resultado += `<option value="${valor.idlocalidad}">${valor.nombre}</option>`;
                });
                $("#lstLocalidad").empty().append(resultado);
            }
        });
    }

    function fBuscarPrecio() {
        let idProducto = $("#lstProducto option:selected").val();
        $.ajax({
            type: "GET",
            url: "venta-formulario.php?do=buscarProducto",
            data: {
                id: idProducto
            },
            async: true,
            dataType: "json",
            success: function(respuesta) {
                let strResultado = Intl.NumberFormat("es-AR", {
                    style: 'currency',
                    currency: 'ARS'
                }).format(respuesta.precio);
                $("#txtPrecioUniCurrency").val(strResultado);
                $("#txtPreciounitario").val(respuesta.precio);
            }
        });
    }

    function fCalcularTotal() {
        var idProducto = $("#lstProducto option:selected").val();
        var precio = parseFloat($('#txtPreciounitario').val());
        var cantidad = parseInt($('#txtCantidad').val());

        $.ajax({
            type: "$_GET",
            url: "venta-formulario.php?do=buscarProducto",
            data: {
                id: idProducto
            },
            async: true,
            dataType: "json",
            success: function(respuesta) {
                let resultado = 0,
                    if (cantidad <= parseInt(respuesta.cantidad)) {
                        resultado = precio * cantidad;
                    } else {
                        $("#msgStock").show();
                    }
                strResultado = Intl.NumberFormat("es-AR", {
                    style: 'currency',
                    currency: 'ARS'
                }).format(resultado);
                $('#txtTotal').val(strResultado);
            }
        });
    }
</script>
<?php include_once("footer.php"); ?>