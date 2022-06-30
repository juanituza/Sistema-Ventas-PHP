<?php

include_once("config.php");
include_once("entidades/producto.php");
include_once("entidades/tipoproducto.php");

$pg = "Listado de Productos";

$producto = new Producto();
$producto->cargarFormulario($_REQUEST);


if ($_POST) {
    if (isset($_POST["btnGuardar"])) {
       if (isset($_GET["id"]) && $_GET["id"] > 0) {
        // buscar el producto para extraer el nombre anterior de la imagen por el id
           $productoAux= new Producto();//genero un nuevo producto
           $productoAux->idproducto= $_GET['id'];// traigo los datos por su id
           $productoAux->obtenerPorId();// traigo los datos por su id
           //subo la imagen nueva
            if ($_FILES["fileImagen"]["error"] === UPLOAD_ERR_OK) {
                if (file_exists("files/" . $productoAux->imagen)) {
                    unlink("files/" . $productoAux->imagen);
                }

                $nombreAleatorio = date("Ymdhmsi");
                $extension = pathinfo($_FILES["fileImagen"]["name"], PATHINFO_EXTENSION);
                $archivo_tpm = $_FILES["fileImagen"]["tmp_name"];
                $nombreDeImagen = $producto->imagen = "$nombreAleatorio.$extension";
                if ($extension == "jpg" || $extension == "jpeg" || $extension == "png") {
                    move_uploaded_file($archivo_tpm, "files/$nombreAleatorio.$extension");
                    $producto->imagen = "$nombreAleatorio.$extension";
                }
            }else{//sino la imagen es la misma
                    $producto->imagen = $productoAux->imagen;
            }                
            //Actualizo un cliente existente
            $producto->actualizar();
            
        } else {
            if ($_FILES["fileImagen"]["error"] === UPLOAD_ERR_OK) {

                $nombreAleatorio = date("Ymdhmsi");
                $archivo_tpm = $_FILES["fileImagen"]["tmp_name"];
                $extension = pathinfo($_FILES["fileImagen"]["name"], PATHINFO_EXTENSION);
                if ($extension == "jpg" || $extension == "jpeg" || $extension == "png") {
                    move_uploaded_file($archivo_tpm, "files/$nombreAleatorio.$extension");
                    $producto->imagen = "$nombreAleatorio.$extension";
                }
            }    
                   
            //Es nuevo
            $producto->insertar();
        }
        $msg["texto"] = "Guardado correctamente";
        $msg["codigo"] = "alert-success";
    } else if (isset($_POST["btnBorrar"])) {
        $productoAux = new Producto();
        $productoAux->idproducto = $_GET["id"];
        $productoAux->obtenerPorId();
        if ($productoAux->imagen!=" " && file_exists("files/" . $productoAux->imagen)) {
            unlink("files/" . $productoAux->imagen);
        }
        $producto->eliminar();
        header("Location: producto-listado.php");
    }
}

if (isset($_GET["do"]) && $_GET["do"] == "buscarTipoProducto" && $_GET["id"] && $_GET["id"] > 0) {
    $idTipoProducto = $_GET["id"];
    $tipoProducto = new TipoProducto();
    $aTipoproductos = $tipoproducto->obtenerTipoProducto($idTipoProducto);
    echo json_encode($aTipoProductos);
    exit;
}
if (isset($_GET["id"]) && $_GET["id"] > 0) {
    $producto->obtenerPorId();
}

$tipoProducto = new TipoProducto();
$aTipoProductos = $tipoProducto->obtenerTodos();



include_once("header.php");
?>
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Producto</h1>
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
            <a href="producto-listado.php" class="btn btn-primary mr-2">Listado</a>
            <a href="producto-formulario.php" class="btn btn-primary mr-2">Nuevo</a>
            <button type="submit" class="btn btn-success mr-2" id="btnGuardar" name="btnGuardar">Guardar</button>
            <button type="submit" class="btn btn-danger" id="btnBorrar" name="btnBorrar">Borrar</button>
        </div>
    </div>
    <div class="row">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="col-6 form-group">
                <label for="txtNombre">Nombre:</label>
                <input type="text" required class="form-control" name="txtNombre" id="txtNombre" value="<?php echo $producto->nombre ?>">
            </div>

            <div class="col-6 form-group">
                <label for="lstTipoProducto">Tipo Producto:</label>
                <select class="form-control" name="lstTipoProducto" id="lstTipoProducto" onchange="" required>
                    <option value="" disabled selected>Seleccionar</option>
                    <?php foreach ($aTipoProductos as $tipoProducto) : ?>
                        <?php if ($producto->fk_idtipoproducto == $tipoProducto->idtipoproducto) : ?>
                            <option selected value="<?php echo $tipoProducto->idtipoproducto; ?>"><?php echo $tipoProducto->nombre; ?></option>
                        <?php else : ?>
                            <option value="<?php echo $tipoProducto->idtipoproducto; ?>"><?php echo $tipoProducto->nombre; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 form-group">
                <label for="txtCantidad">Cantidad:</label>
                <input type="number"  class="form-control" name="txtCantidad" id="txtCantidad" value="<?php echo $producto->cantidad ?>">
            </div>
            <div class="col-6 form-group">
                <label for="txtPrecio">Precio:</label>
                <input type="text" class="form-control" name="txtPrecio" id="txtPrecio"  value="<?php echo $producto->precio ?>">
            </div>
            <div class="col-12 form-group">
                <label for="txtDescripcion">Descripción:</label>
                <textarea style="resize:none" class="form-control" name="txtDescripcion" id="txtDescripcion"><?php echo $producto->descripcion ?></textarea>
            </div>
            <div>
                <label for="fileImagen">Imagen:</label>
                <input type="file" name="fileImagen" id="fileImagen" class="shadow" accept=".jpg,.jpeg,.png">
                <p>Archivos admitidos: .jpg .jpeg .png</p>
            </div>
        </form>
    </div>


</div>


<?php include_once("footer.php"); ?>

<script>
    ClassicEditor
        .create(document.querySelector('#txtDescripcion'))
        .catch(error => {
            console.error(error);
        });
</script>