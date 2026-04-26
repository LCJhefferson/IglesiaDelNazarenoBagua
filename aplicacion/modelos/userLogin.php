<?php
namespace aplicacion\modelos;

class userLogin {
    private $id;
    private $username;
    private $password;
    private $id_rol;
    private $estado;

    public function __construct(
        $username, $password, $id_rol, $estado,
        $id = null
    ) {
        $this->id       = $id;
        $this->username = $username;
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->id_rol   = $id_rol;
        $this->estado   = $estado;
    }

    // ── GETTERS ──
    public function getId()       { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getPassword() { return $this->password; }
    public function getIdRol()    { return $this->id_rol; }
    public function getEstado()   { return $this->estado; }

    // ── SETTERS ──
    public function setUsername($username) { $this->username = $username; }
    public function setPassword($password) { $this->password = password_hash($password, PASSWORD_BCRYPT); }
    public function setIdRol($id_rol)      { $this->id_rol   = $id_rol; }
    public function setEstado($estado)     { $this->estado   = $estado; }
}