<?php
  include('../config/conexion.php');

  //tabla facultades
  try {
    $db->beginTransaction();
    $query_departamentos = "CREATE TABLE IF NOT EXISTS facultades (
      facultad varchar(100) PRIMARY KEY
      )";
    $db->exec($query_departamentos);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla carreras
  try {
    $db->beginTransaction();
    $query_carreras = "CREATE TABLE IF NOT EXISTS carreras (
      carrera varchar(100) PRIMARY KEY
    )";
    $db->exec($query_carreras);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla nom_planes
  try {
    $db->beginTransaction();
    $query_nom_planes = "CREATE TABLE IF NOT EXISTS nom_planes (
      codigo_plan varchar(4) PRIMARY KEY,
      nombre varchar(100) NOT NULL
    )";
    $db->exec($query_nom_planes);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla planes
  try {
    $db->beginTransaction();
    $query_planes = "CREATE TABLE IF NOT EXISTS planes (
      codigo_plan varchar(4) PRIMARY KEY,
      facultad varchar(100) NOT NULL REFERENCES facultades(facultad),
      carrera varchar(100) NOT NULL REFERENCES carreras(carrera),
      jornada varchar(100) NOT NULL CHECK (jornada IN ('Diurno', 'Vespertino')),
      sede varchar(100) NOT NULL CHECK (sede IN ('Uagadou', 'Hogwarts', 'Beauxbaton')),
      grado varchar(100) NOT NULL CHECK (grado IN ('Pregrado', 'Postgrado', 'Programa Especial')),
      modalidad varchar(100) NOT NULL CHECK (modalidad IN ('Presencial', 'OnLine')),
      inicio DATE NOT NULL
    )";
    $db->exec($query_planes);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla nom_planes
  try {
    $db->beginTransaction();
    $query_nom_planes = "CREATE TABLE IF NOT EXISTS nom_planes (
      id_nom_plan SERIAL PRIMARY KEY,
      codigo_plan varchar(4) NOT NULL REFERENCES planes(codigo_plan),
      nombre varchar(100) NOT NULL
    )";
    $db->exec($query_nom_planes);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla departamentos
  try {
    $db->beginTransaction();
    $query_departamentos = "CREATE TABLE IF NOT EXISTS departamentos (
      id_depto INT PRIMARY KEY,
      departamento varchar(100) NOT NULL,
      facultad varchar(100) NOT NULL REFERENCES facultades(facultad)
    )";
    $db->exec($query_departamentos);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }
  

  //tabla asignaturas
  try {
    $db->beginTransaction();
    $query_asignaturas = "CREATE TABLE IF NOT EXISTS asignaturas (
      id_asignatura varchar(12) PRIMARY KEY,
      id_plan varchar(8) NOT NULL REFERENCES planes(codigo_plan),
      asignatura varchar(100) NOT NULL,
      nivel INT NOT NULL CHECK (nivel BETWEEN 1 AND 10),
      pre_requisito varchar(1)
    )";
    $db->exec($query_asignaturas);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla prerequisitos
  try {
    $db->beginTransaction();
    $query_prerequisitos = "CREATE TABLE IF NOT EXISTS prerequisitos (
      id_plan varchar(8) NOT NULL REFERENCES planes(codigo_plan),
      id_asignatura varchar(8) NOT NULL REFERENCES asignaturas(id_asignatura),
      pre1 varchar(8) NOT NULL,
      pre2 varchar(8) 
    )";
    $db->exec($query_prerequisitos);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla personas
  try {
    $db->beginTransaction();
    $query_personas = "CREATE TABLE IF NOT EXISTS personas (
      run INT PRIMARY KEY,
      dv varchar(1) NOT NULL,
      nombre varchar(100) NOT NULL,
      estamento varchar(100) 
    )";
    $db->exec($query_personas);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla estudiantes
  try {
    $db->beginTransaction();
    $query_estudiantes = "CREATE TABLE IF NOT EXISTS estudiantes (
      n_alumno INT PRIMARY KEY,
      cohorte varchar(8) NOT NULL,
      bloqueo BOOLEAN,
      causal_bloqueo varchar(500),
      id_plan varchar(8) NOT NULL REFERENCES planes(codigo_plan),
      run INT NOT NULL REFERENCES personas(run),
      logro varchar(100),
      fecha_logro varchar(8),
      ult_carga varchar(8)
    )";
    $db->exec($query_estudiantes);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla exalumnos
  try {
    $db->beginTransaction();
    $query_exalumnos = "CREATE TABLE IF NOT EXISTS exalumnos (
      n_alumno INT PRIMARY KEY,
      cohorte varchar(8) NOT NULL,
      id_plan varchar(8) NOT NULL REFERENCES planes(codigo_plan),
      run INT NOT NULL REFERENCES personas(run),
      ult_carga varchar(8),
      titulo varchar(100)
    )";
    $db->exec($query_exalumnos);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla notas
  try {
    $db->beginTransaction();
    $query_notas = "CREATE TABLE IF NOT EXISTS notas (
      id_nota SERIAL PRIMARY KEY,
      run INT NOT NULL REFERENCES personas(run),
      id_plan varchar(12) NOT NULL REFERENCES planes(codigo_plan),
      id_asignatura varchar(12) NOT NULL REFERENCES asignaturas(id_asignatura),
      periodo varchar(12) NOT NULL,
      convocatoria varchar(3),
      calificacion varchar(3),
      nota FLOAT CHECK (nota BETWEEN 1.0 AND 7.0)
    )";
    $db->exec($query_notas);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla profesores
  try {
    $db->beginTransaction();
    $query_profesores = "CREATE TABLE IF NOT EXISTS profesores (
      run INT PRIMARY KEY REFERENCES personas(run),
      dedicacion varchar(100) NOT NULL,
      contrato varchar(100) NOT NULL,
      diurno BOOLEAN,
      vespertino BOOLEAN,
      sede varchar(100),
      carrera varchar(100),
      grado varchar(100),
      jerarquia varchar(100),
      cargo varchar(100)
    )";
    $db->exec($query_profesores);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla administrativos
  try {
    $db->beginTransaction();
    $query_administrativos = "CREATE TABLE IF NOT EXISTS administrativos (
      run INT PRIMARY KEY REFERENCES personas(run),
      dedicacion varchar(100),
      contrato varchar(100),
      cargo varchar(100)
    )";
    $db->exec($query_administrativos);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //tabla mails
  try {
    $db->beginTransaction();
    $query_mails = "CREATE TABLE IF NOT EXISTS mails (
      id_mail SERIAL PRIMARY KEY,
      mail_inst varchar(100) NOT NULL,
      mail_pers varchar(100),
      run INT NOT NULL REFERENCES personas(run)
    )";
    $db->exec($query_mails);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }
      

  //tabla usuarios
  try {
    $db->beginTransaction();
    $query_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
      id_user SERIAL PRIMARY KEY,
      run INT NOT NULL REFERENCES personas(run),
      email varchar(100) UNIQUE NOT NULL,
      password_hash varchar(255) NOT NULL,
      rol varchar(100) NOT NULL CHECK (rol IN ('admin', 'user')),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($query_usuarios);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //crear administrador
  // try {
  //   $email = 'bananer@lamejor.com';
  //   $password_hash = password_hash('bananer0', PASSWORD_DEFAULT);
  //   $rol = 'admin';

  //   //crear persona de administrador
  //   $db->beginTransaction();
  //   $query_persona = "INSERT INTO personas (run, dv, nombre, estamento) VALUES (00000, '0', 'Bananer', 'admin')";
  //   $db->exec($query_persona);
  //   $db->commit();
    
  //   $db->beginTransaction();
  //   $query_admin = "INSERT INTO usuarios (run, email, password_hash, rol) VALUES (00000, '$email', '$password_hash', '$rol')";
  //   $db->exec($query_admin);
  //   $db->commit();
  // } catch (Exception $e) {
  //   $db->rollBack();
  //   echo "Failed: " . $e->getMessage();
  // }

  // echo "Tablas creadas con éxito";


?>