<?php
require_once "conexion.php";
class ModeloUsuario
{
  /* metodo de acceso al sistema */
  static public function mdlAccesoUsuario($usuario)
  {
    $stmt = Conexion::conectar()->prepare("SELECT * FROM usuario WHERE email = :email");
    $stmt->bindParam(":email", $usuario, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch();
  }

//comprobar acceso almacen
  static public function mdlAccesoAlmacen($idUsuario, $almacen){
    //preguntando el id del permiso segun el almacen
    $stmt1 = Conexion::conectar()->prepare("SELECT id_permiso FROM permiso WHERE desc_permiso='$almacen'");
    $stmt1->execute();
    $resultado= $stmt1->fetch();
    $idPermiso=$resultado["id_permiso"];
    
    $stmt = Conexion::conectar()->prepare("SELECT count(*) as permiso FROM permiso_usuario WHERE id_usuario=$idUsuario AND id_permiso= $idPermiso");
    $stmt->execute();
    return $stmt->fetch();
  }
  
  static public function mdlInfoUsuarios()
  {
    $stmt = Conexion::conectar()->prepare("SELECT * FROM usuario");
    $stmt->execute();
    $resultado = $stmt->fetchAll();
    $stmt = null;
    return $resultado;
  }

  static public function mdlRegistrarDesdePanel($datos)
  {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO usuario (nombre, email, password, categoria, estado_usuario)
       VALUES (:nombre, :email, :password, :categoria, :estado)"
    );
    return $stmt->execute($datos);
  }

  static public function mdlActualizarDesdePanel($id, $datos)
  {
    $campos = "nombre = :nombre, email = :email, categoria = :categoria, estado_usuario = :estado";
    $parametros = [
      ":nombre" => $datos[":nombre"],
      ":email" => $datos[":email"],
      ":categoria" => $datos[":categoria"],
      ":estado" => $datos[":estado"],
      ":id" => $id
    ];
    if ($datos[":password"] !== "") {
      $campos .= ", password = :password";
      $parametros[":password"] = $datos[":password"];
    }
    $stmt = Conexion::conectar()->prepare("UPDATE usuario SET $campos WHERE id_usuario = :id");
    return $stmt->execute($parametros);
  }


  static public function mdlRegUsuario($data)
  {
    $emailUsuario = $data["emailUsuario"];
    $nomUsuario = $data["nomUsuario"];
    $passUsuario = $data["passUsuario"];

    $stmt = Conexion::conectar()->prepare("INSERT INTO usuario(nombre, email, password) VALUES(:nombre, :email, :password)");

    $stmt->bindParam(":nombre", $nomUsuario, PDO::PARAM_STR);
    $stmt->bindParam(":email", $emailUsuario, PDO::PARAM_STR);
    $stmt->bindParam(":password", $passUsuario, PDO::PARAM_STR);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }

    $stmt = null;
  }


  static public function mdlInfoUsuario($id)
  {
    $stmt = Conexion::conectar()->prepare("SELECT * FROM usuario WHERE id_usuario = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();
    $stmt = null;
    return $usuario;
  }


  static public function mdlEditUsuario($data)
  {
    $idUsuario = $data["idUsuario"];
    $nomUsuario = $data["nomUsuario"];
    $passUsuario = $data["passUsuario"];
    $catUsuario = $data["catUsuario"];


    $stmt = Conexion::conectar()->prepare("UPDATE usuario SET nombre = :nombre, categoria = :categoria, password = :password WHERE id_usuario = :id");

    $stmt->bindParam(":nombre", $nomUsuario, PDO::PARAM_STR);
    $stmt->bindParam(":categoria", $catUsuario, PDO::PARAM_STR);
    $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
    $stmt->bindParam(":password", $passUsuario, PDO::PARAM_STR);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }

    $stmt->null;
  }

  static public function mdlEliUsuario($id)
  {
    $stmt = Conexion::conectar()->prepare("DELETE FROM usuario WHERE id_usuario = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
  }

  static public function mdlCambioEstado($estado, $id){
    $stmt=Conexion::conectar()->prepare("UPDATE usuario SET estado_usuario='$estado' WHERE id_usuario=$id");

    if($stmt->execute()){
      $stmt->closeCursor();
      return "ok";
    }else{
      return "error";
    }
  }

  static public function mdlCantidadUsuarios()
  {
    $stmt = Conexion::conectar()->prepare("select count(id_usuario) as usuarios from usuario");

    $stmt->execute();
    return $stmt->fetch();

    $stmt->close();
    $stmt->null;
  }

  //PERMISOS DE USUARIO
  static public function mdlUsuarioPermiso($idUsuario, $idPermiso)
  {
    $stmt = Conexion::conectar()->prepare("select * from permiso_usuario where id_usuario=$idUsuario and id_permiso=$idPermiso");

    $stmt->execute();
    return $stmt->fetch();
    $stmt->close();
    $stmt->null;
  }


  static public function mdlActualizarPermiso($data)
  {
    $idUsuario = $data["idUsuario"];
    $idPermiso = $data["idPermiso"];

    $permiso = Conexion::conectar()->prepare("select * from permiso_usuario where id_usuario=$idUsuario and id_permiso=$idPermiso");
    $permiso->execute();

    if (($permiso->fetch()) != null) {
      $stmt = Conexion::conectar()->prepare("delete from permiso_usuario  where id_usuario=$idUsuario and id_permiso=$idPermiso");
    } else {
      $stmt = Conexion::conectar()->prepare("insert into permiso_usuario (id_usuario, id_permiso) values ($idUsuario, $idPermiso)");
    }

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }

    $stmt->close();
    $stmt->null;
  }

  static public function mdlListaPermisos(){
    $stmt = Conexion::conectar()->prepare("select * from permiso");

    $stmt->execute();
    $resul = $stmt->fetchAll();
    $stmt->closeCursor();
    return $resul;
  }
}
