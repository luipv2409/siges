-- ============================================================
-- SIGES - Sistema de Gestión de Empeños
-- Esquema de Base de Datos - Fase 1
-- Motor: MySQL 8.0+ / MariaDB 10.4+
-- Charset: utf8mb4
-- ============================================================

-- Eliminar base de datos si existe (para re-ejecución limpia)
DROP DATABASE IF EXISTS siges;

-- Crear base de datos
CREATE DATABASE siges
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE siges;

-- ============================================================
-- TABLA: roles
-- Almacena los roles del sistema (OWNER, EMPLOYEE, CLIENT)
-- ============================================================
CREATE TABLE roles (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name        VARCHAR(50)      NOT NULL,
    description VARCHAR(255)     NULL,
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: users
-- Almacena los usuarios del sistema (dueños y empleados)
-- ============================================================
CREATE TABLE users (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    role_id       INT UNSIGNED     NOT NULL,
    name          VARCHAR(100)     NOT NULL,
    email         VARCHAR(150)     NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    phone         VARCHAR(20)      NULL,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    last_login_at DATETIME         NULL,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_id (role_id),
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: customers
-- Almacena los clientes de la casa de empeños
-- ============================================================
CREATE TABLE customers (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED     NULL,
    ci            VARCHAR(20)      NOT NULL,
    first_name    VARCHAR(100)     NOT NULL,
    last_name     VARCHAR(100)     NOT NULL,
    email         VARCHAR(150)     NULL,
    phone         VARCHAR(20)      NOT NULL,
    address       VARCHAR(255)     NULL,
    birth_date    DATE             NULL,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_customers_ci (ci),
    UNIQUE KEY uq_customers_email (email),
    KEY idx_customers_user_id (user_id),
    CONSTRAINT fk_customers_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: password_resets
-- Almacena tokens de recuperación de contraseña
-- ============================================================
CREATE TABLE password_resets (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED     NOT NULL,
    token_hash  VARCHAR(64)      NOT NULL,
    expires_at  DATETIME         NOT NULL,
    used_at     DATETIME         NULL,
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_password_resets_token (token_hash),
    KEY idx_password_resets_user (user_id),
    KEY idx_password_resets_expires (expires_at),
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: audit_log
-- Registra intentos de acceso no autorizado y acciones sensibles
-- ============================================================
CREATE TABLE audit_log (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED     NULL,
    action      VARCHAR(100)     NOT NULL,
    description VARCHAR(500)     NULL,
    ip_address  VARCHAR(45)      NULL,
    user_agent  VARCHAR(255)     NULL,
    created_at  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_log_user (user_id),
    KEY idx_audit_log_action (action),
    KEY idx_audit_log_created (created_at),
    CONSTRAINT fk_audit_log_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERTS INICIALES
-- ============================================================

-- Roles del sistema
INSERT INTO roles (name, description) VALUES
    ('OWNER',    'Dueño del negocio. Acceso total al sistema: reportes, configuraciones, empeños, subastas e inventario.'),
    ('EMPLOYEE', 'Empleado de la casa de empeños. Gestiona empeños, valuación, subastas, inventario y notificaciones.'),
    ('CLIENT',   'Cliente de la casa de empeños. Puede ver subastas, participar en ellas, ver sus empeños y su perfil.');

-- Usuario administrador inicial (OWNER)
-- Contraseña: Admin123! (hash generado con password_hash de PHP, algoritmo PASSWORD_DEFAULT)
INSERT INTO users (role_id, name, email, password_hash, phone, is_active) VALUES
    (1, 'Administrador SIGES', 'admin@siges.com', '$2y$10$76/wAf.q3.oNaDBd/H/eCuEEYEbJg0wq47KD3G6b0LtGhnVdKOUkG', '77712345', 1);


