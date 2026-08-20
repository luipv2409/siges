#!/usr/bin/env python3
"""
================================================================================
 SIGES - Test Suite Sprint 1 (ADAPTADO PARA ARCHIVOS PHP DIRECTOS)
 EPIC-01: Autenticación, Seguridad y Control de Accesos
================================================================================
Estructura de archivos identificada:
  - login.php           → Página de login
  - register.php        → Registro de clientes
  - recover.php         → Recuperación de contraseña
  - reset_password.php  → Restablecer contraseña
  - dashboard.php       → Dashboard protegido
  - usuarios.php        → Panel de gestión de usuarios
  - logout.php          → Cierre de sesión
================================================================================
CÓMO USARLO:
  1. Asegúrate de que XAMPP esté corriendo
  2. Ejecuta:
       python test_sprint1_siges.py --base-url http://localhost/siges
================================================================================
"""

import argparse
import json
import re
import sys
import time
import uuid
from dataclasses import dataclass, field
from typing import Callable, Optional

import requests
from requests.exceptions import ConnectionError as ReqConnectionError
from requests.exceptions import RequestException, Timeout

# ==============================================================================
# CONFIG — ADAPTADO A TU ESTRUCTURA
# ==============================================================================

CONFIG = {
    "base_url": "http://localhost/siges",
    "timeout": 8,

    # Rutas (archivos PHP directos)
    "routes": {
        "login_page":        "/login.php",
        "login_api":         "/login.php",          # El login procesa POST a sí mismo
        "logout":            "/logout.php",
        "register_page":     "/register.php",
        "register_api":      "/register.php",       # El registro procesa POST a sí mismo
        "forgot_page":       "/recover.php",
        "forgot_api":        "/recover.php",        # Recuperación procesa POST a sí mismo
        "reset_page":        "/reset_password.php",
        "reset_api":         "/reset_password.php", # Reset procesa POST a sí mismo
        "dashboard":         "/dashboard.php",
        "usuarios_page":     "/usuarios.php",       # Panel de gestión de usuarios
        "config_page":       "/configuracion.php",  # Configuración (si existe)
        "pawns_page":        "/empenos.php",        # Módulo de empeños (si existe)
    },

    # Nombres de campos del formulario (ajusta según tu HTML)
    "fields": {
        "email":    "email",
        "password": "password",
        "remember": "remember",
        "csrf":     "csrf_token",
        "ci":       "ci",
        "name":     "name",
        "phone":    "phone",
        "address":  "address",
        "confirm_password": "password_confirmation",
    },

    # Usuarios de prueba (debes tenerlos en tu base de datos)
    "users": {
        "owner":    {"email": "admin@siges.com",  "password": "Admin123!",  "role": "OWNER"},
        "employee": {"email": "samuel@siges.com", "password": "12345678",   "role": "EMPLOYEE"},
        "client":   {"email": "gunnar@gmail.com", "password": "12345678",   "role": "CLIENT"},
    },

    # HTTP status esperados
    "expected_status": {
        "login_ok": (200, 302),
        "unauthenticated": (302, 401),
        "forbidden": (403,),
        "not_found_soft": (200, 302, 401, 403, 404),
    },
}

# ==============================================================================
# FRAMEWORK LIGERO DE TESTING (mismo que antes)
# ==============================================================================

ANSI = {
    "reset": "\033[0m", "bold": "\033[1m", "dim": "\033[2m",
    "green": "\033[32m", "red": "\033[31m", "yellow": "\033[33m",
    "blue": "\033[34m", "cyan": "\033[36m", "magenta": "\033[35m",
}

def c(text, color):
    return f"{ANSI.get(color,'')}{text}{ANSI['reset']}"

@dataclass
class TestResult:
    hu: str
    ticket: str
    name: str
    status: str
    detail: str = ""
    duration_ms: float = 0.0

@dataclass
class TestCase:
    hu: str
    ticket: str
    name: str
    func: Callable
    group: str

class TestRunner:
    def __init__(self, config, verbose=False, only_groups=None):
        self.config = config
        self.verbose = verbose
        self.only_groups = only_groups
        self.tests: list[TestCase] = []
        self.results: list[TestResult] = []
        self.session_owner = requests.Session()
        self.session_employee = requests.Session()
        self.session_client = requests.Session()
        self.session_anon = requests.Session()
        self.base = config["base_url"].rstrip("/")
        self.server_reachable = None

    def register(self, hu, ticket, name, group):
        def deco(func):
            self.tests.append(TestCase(hu, ticket, name, func, group))
            return func
        return deco

    def url(self, key_or_path):
        routes = self.config["routes"]
        path = routes.get(key_or_path, key_or_path)
        return self.base + path

    def check_server(self):
        try:
            requests.get(self.base, timeout=self.config["timeout"])
            self.server_reachable = True
        except (ReqConnectionError, Timeout):
            self.server_reachable = False
        except RequestException:
            self.server_reachable = True

    def run(self):
        self.check_server()
        if not self.server_reachable:
            print(c("\n✖ No se pudo conectar a " + self.base, "red"))
            print(c("  Verifica que Apache/XAMPP esté corriendo.", "yellow"))
            print(c("  Ajusta la URL con --base-url http://localhost/tu-carpeta\n", "yellow"))
            for t in self.tests:
                self.results.append(TestResult(t.hu, t.ticket, t.name, "SKIP", "Servidor no disponible"))
            return

        print(c(f"\n{'='*78}", "cyan"))
        print(c(f"  SIGES · Test Suite Sprint 1  →  {self.base}", "bold"))
        print(c(f"{'='*78}\n", "cyan"))

        current_group = None
        for t in self.tests:
            if self.only_groups and t.group not in self.only_groups:
                continue
            if t.group != current_group:
                current_group = t.group
                print(c(f"\n▶ {current_group}", "magenta"))

            start = time.time()
            try:
                ok, detail = t.func(self)
                status = "PASS" if ok else "FAIL"
            except AssertionError as e:
                status, detail = "FAIL", str(e)
            except (ReqConnectionError, Timeout):
                status, detail = "SKIP", "No se pudo conectar / timeout"
            except Exception as e:
                status, detail = "ERROR", f"{type(e).__name__}: {e}"
            duration = (time.time() - start) * 1000
            self.results.append(TestResult(t.hu, t.ticket, t.name, status, detail, duration))
            self._print_line(t, status, detail, duration)

        self._print_summary()

    def _print_line(self, t, status, detail, duration):
        color = {"PASS": "green", "FAIL": "red", "SKIP": "yellow", "ERROR": "red"}[status]
        icon = {"PASS": "✔", "FAIL": "✖", "SKIP": "…", "ERROR": "‼"}[status]
        line = f"  {c(icon, color)} [{t.ticket:>7}] {t.name}"
        print(line)
        if status in ("FAIL", "ERROR") or (self.verbose and detail):
            print(c(f"        → {detail}", "dim"))
        if self.verbose:
            print(c(f"        ({duration:.0f} ms)", "dim"))

    def _print_summary(self):
        print(c(f"\n{'='*78}", "cyan"))
        print(c("  RESUMEN POR HISTORIA DE USUARIO", "bold"))
        print(c(f"{'='*78}", "cyan"))

        by_hu = {}
        for r in self.results:
            by_hu.setdefault(r.hu, []).append(r)

        for hu, results in by_hu.items():
            passed = sum(1 for r in results if r.status == "PASS")
            failed = sum(1 for r in results if r.status == "FAIL")
            errored = sum(1 for r in results if r.status == "ERROR")
            skipped = sum(1 for r in results if r.status == "SKIP")
            total = len(results)
            pct = (passed / total * 100) if total else 0
            bar_color = "green" if failed == 0 and errored == 0 else ("yellow" if pct >= 50 else "red")
            print(f"  {c(hu, 'bold'):40s}  {c(f'{passed}/{total} OK', bar_color)}"
                  f"  (fail={failed} error={errored} skip={skipped})")

        total = len(self.results)
        passed = sum(1 for r in self.results if r.status == "PASS")
        failed = sum(1 for r in self.results if r.status == "FAIL")
        errored = sum(1 for r in self.results if r.status == "ERROR")
        skipped = sum(1 for r in self.results if r.status == "SKIP")

        print(c(f"\n{'-'*78}", "cyan"))
        print(f"  TOTAL: {total}   "
              f"{c(f'PASS={passed}', 'green')}   "
              f"{c(f'FAIL={failed}', 'red')}   "
              f"{c(f'ERROR={errored}', 'red')}   "
              f"{c(f'SKIP={skipped}', 'yellow')}")
        overall = "green" if failed == 0 and errored == 0 and passed > 0 else "red"
        verdict = "SPRINT 1 CUMPLE LOS CRITERIOS PROBADOS" if overall == "green" else "SPRINT 1 TIENE FALLAS PENDIENTES"
        print(c(f"\n  >> {verdict}", overall))
        print(c(f"{'='*78}\n", "cyan"))

    def export_json(self, path):
        data = [r.__dict__ for r in self.results]
        with open(path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=2, ensure_ascii=False)
        print(c(f"\nReporte JSON guardado en: {path}", "cyan"))

runner_registry = TestRunner(CONFIG)

def test(hu, ticket, name, group):
    return runner_registry.register(hu, ticket, name, group)

# ==============================================================================
# UTILIDADES DE PRUEBA
# ==============================================================================

def do_login(runner: TestRunner, session: requests.Session, email: str, password: str):
    f = runner.config["fields"]
    payload = {f["email"]: email, f["password"]: password}
    resp = session.post(runner.url("login_api"), data=payload,
                         timeout=runner.config["timeout"], allow_redirects=False)
    return resp

def looks_like_generic_error(text: str) -> bool:
    """Verifica que el mensaje de error NO revele si el usuario existe."""
    lowered = text.lower()
    leaky_phrases = [
        "no existe el usuario", "usuario no encontrado", "email no registrado",
        "no such user", "user not found", "email does not exist",
    ]
    return not any(p in lowered for p in leaky_phrases)

# ==============================================================================
# HU-01.01 · LOGIN (login.php)
# ==============================================================================

@test("HU-01.01", "SGS-26", "Página de login carga y contiene formulario", "HU-01.01 Login")
def t_login_page_loads(r: TestRunner):
    resp = requests.get(r.url("login_page"), timeout=r.config["timeout"])
    assert resp.status_code == 200, f"Status inesperado: {resp.status_code}"
    body = resp.text.lower()
    f = r.config["fields"]
    has_email = f["email"] in body or "type=\"email\"" in body
    has_pass = f["password"] in body or "type=\"password\"" in body
    assert has_email and has_pass, "No se detectan campos email/password"
    return True, "Formulario de login detectado"

@test("HU-01.01", "SGS-27", "Login exitoso con OWNER", "HU-01.01 Login")
def t_login_owner_ok(r: TestRunner):
    u = r.config["users"]["owner"]
    resp = do_login(r, r.session_owner, u["email"], u["password"])
    ok = resp.status_code in r.config["expected_status"]["login_ok"]
    has_cookie = bool(r.session_owner.cookies.get_dict())
    assert ok, f"Status {resp.status_code} inesperado"
    assert has_cookie, "No se estableció cookie de sesión"
    return True, f"Login OWNER OK (status={resp.status_code})"

@test("HU-01.01", "SGS-27", "Login exitoso con EMPLOYEE", "HU-01.01 Login")
def t_login_employee_ok(r: TestRunner):
    u = r.config["users"]["employee"]
    resp = do_login(r, r.session_employee, u["email"], u["password"])
    ok = resp.status_code in r.config["expected_status"]["login_ok"]
    assert ok, f"Status {resp.status_code} inesperado"
    return True, f"Login EMPLOYEE OK (status={resp.status_code})"

@test("HU-01.01", "SGS-27", "Login exitoso con CLIENT", "HU-01.01 Login")
def t_login_client_ok(r: TestRunner):
    u = r.config["users"]["client"]
    resp = do_login(r, r.session_client, u["email"], u["password"])
    ok = resp.status_code in r.config["expected_status"]["login_ok"]
    assert ok, f"Status {resp.status_code} inesperado"
    return True, f"Login CLIENT OK (status={resp.status_code})"

@test("HU-01.01", "SGS-27", "Login falla con contraseña incorrecta", "HU-01.01 Login")
def t_login_wrong_password(r: TestRunner):
    u = r.config["users"]["owner"]
    s = requests.Session()
    resp = do_login(r, s, u["email"], "clave-incorrecta-123")
    is_rejected = resp.status_code in (200, 302, 401) and not s.cookies.get_dict()
    assert is_rejected, "El sistema aceptó una contraseña incorrecta"
    return True, "Contraseña incorrecta rechazada"

@test("HU-01.01", "SGS-27", "Login rechaza inyección SQL", "HU-01.01 Login")
def t_login_sql_injection(r: TestRunner):
    s = requests.Session()
    payloads = [
        "' OR '1'='1", "' OR 1=1 -- -", "admin'--",
    ]
    for p in payloads:
        resp = do_login(r, s, p, p)
        assert not s.cookies.get_dict(), f"¡Posible SQLi con payload: {p}!"
        assert resp.status_code < 500, f"Error 500 con payload '{p}'"
    return True, f"{len(payloads)} payloads de SQLi probados sin bypass"

# ==============================================================================
# HU-01.02 · ROLES Y AUTORIZACIÓN (SGS-28, SGS-29, SGS-30)
# ==============================================================================

@test("HU-01.02", "SGS-29", "Acceso a dashboard sin sesión → redirige", "HU-01.02 Roles")
def t_unauthenticated_blocked(r: TestRunner):
    resp = requests.get(r.url("dashboard"), timeout=r.config["timeout"], allow_redirects=False)
    ok = resp.status_code in r.config["expected_status"]["unauthenticated"]
    assert ok, f"Dashboard respondió {resp.status_code} sin sesión"
    return True, f"Dashboard sin sesión → status {resp.status_code}"

@test("HU-01.02", "SGS-30", "CLIENT no puede acceder a usuarios.php", "HU-01.02 Roles")
def t_client_forbidden_usuarios(r: TestRunner):
    resp = r.session_client.get(r.url("usuarios_page"), timeout=r.config["timeout"], allow_redirects=False)
    allowed_denials = r.config["expected_status"]["forbidden"] + (302, 401)
    assert resp.status_code in allowed_denials, (
        f"CLIENT obtuvo {resp.status_code} en usuarios.php"
    )
    return True, f"CLIENT bloqueado de usuarios.php (status={resp.status_code})"

@test("HU-01.02", "SGS-30", "EMPLOYEE no puede acceder a usuarios.php", "HU-01.02 Roles")
def t_employee_forbidden_usuarios(r: TestRunner):
    resp = r.session_employee.get(r.url("usuarios_page"), timeout=r.config["timeout"], allow_redirects=False)
    allowed_denials = r.config["expected_status"]["forbidden"] + (302, 401)
    assert resp.status_code in allowed_denials, (
        f"EMPLOYEE obtuvo {resp.status_code} en usuarios.php"
    )
    return True, f"EMPLOYEE bloqueado de usuarios.php (status={resp.status_code})"

@test("HU-01.02", "SGS-30", "OWNER sí puede acceder a usuarios.php", "HU-01.02 Roles")
def t_owner_allowed_usuarios(r: TestRunner):
    resp = r.session_owner.get(r.url("usuarios_page"), timeout=r.config["timeout"], allow_redirects=False)
    assert resp.status_code in (200, 302), f"OWNER no pudo acceder a usuarios.php ({resp.status_code})"
    return True, f"OWNER accede a usuarios.php (status={resp.status_code})"

# ==============================================================================
# HU-01.03 · REGISTRO DE CLIENTES (register.php)
# ==============================================================================

def _unique_customer_payload(r: TestRunner):
    f = r.config["fields"]
    suffix = uuid.uuid4().hex[:8]
    return {
        f["ci"]: f"CI{suffix}",
        f["name"]: f"Cliente Prueba {suffix}",
        f["email"]: f"cliente.{suffix}@test.com",
        f["phone"]: "70012345",
        f["address"]: "Calle Falsa 123",
        f["password"]: "ClaveSegura123!",
        f["confirm_password"]: "ClaveSegura123!",
    }

@test("HU-01.03", "SGS-32", "Página de registro carga", "HU-01.03 Registro")
def t_register_page_loads(r: TestRunner):
    resp = requests.get(r.url("register_page"), timeout=r.config["timeout"])
    assert resp.status_code == 200, f"Status inesperado: {resp.status_code}"
    body = resp.text.lower()
    f = r.config["fields"]
    required_fields = [f["ci"], f["name"], f["email"], f["phone"]]
    missing = [x for x in required_fields if x.lower() not in body]
    assert not missing, f"Campos ausentes: {missing}"
    return True, "Formulario de registro OK"

@test("HU-01.03", "SGS-33", "Registro de cliente nuevo exitoso", "HU-01.03 Registro")
def t_register_client_success(r: TestRunner):
    payload = _unique_customer_payload(r)
    resp = requests.post(r.url("register_api"), data=payload, timeout=r.config["timeout"])
    assert resp.status_code in (200, 201, 302), f"Registro falló: {resp.status_code}"
    return True, f"Cliente registrado (status={resp.status_code})"

@test("HU-01.03", "SGS-33", "Registro rechaza CI duplicado", "HU-01.03 Registro")
def t_register_duplicate_ci(r: TestRunner):
    base = _unique_customer_payload(r)
    requests.post(r.url("register_api"), data=base, timeout=r.config["timeout"])
    dup = dict(base)
    dup[r.config["fields"]["email"]] = f"otro.{uuid.uuid4().hex[:6]}@test.com"
    resp = requests.post(r.url("register_api"), data=dup, timeout=r.config["timeout"])
    assert resp.status_code in (400, 409, 422), f"Status inesperado: {resp.status_code}"
    return True, "CI duplicado rechazado"

@test("HU-01.03", "SGS-33", "Registro rechaza email duplicado", "HU-01.03 Registro")
def t_register_duplicate_email(r: TestRunner):
    u = r.config["users"]["client"]
    payload = _unique_customer_payload(r)
    payload[r.config["fields"]["email"]] = u["email"]
    resp = requests.post(r.url("register_api"), data=payload, timeout=r.config["timeout"])
    assert resp.status_code in (400, 409, 422), f"Status inesperado: {resp.status_code}"
    return True, "Email duplicado rechazado"

# ==============================================================================
# HU-01.04 · RECUPERAR CONTRASEÑA (recover.php / reset_password.php)
# ==============================================================================

@test("HU-01.04", "SGS-37", "Página de recuperación carga", "HU-01.04 Password/Logout")
def t_forgot_page_loads(r: TestRunner):
    resp = requests.get(r.url("forgot_page"), timeout=r.config["timeout"])
    assert resp.status_code == 200, f"Status inesperado: {resp.status_code}"
    return True, "Página de recuperación OK"

@test("HU-01.04", "SGS-36", "Recuperación con email existente", "HU-01.04 Password/Logout")
def t_forgot_existing_email(r: TestRunner):
    u = r.config["users"]["client"]
    resp = requests.post(r.url("forgot_api"), data={r.config["fields"]["email"]: u["email"]},
                          timeout=r.config["timeout"])
    assert resp.status_code in (200, 202, 302), f"Status inesperado: {resp.status_code}"
    return True, f"Recuperación → status {resp.status_code}"

@test("HU-01.04", "SGS-37", "Logout destruye la sesión", "HU-01.04 Password/Logout")
def t_logout_destroys_session(r: TestRunner):
    s = requests.Session()
    u = r.config["users"]["client"]
    do_login(r, s, u["email"], u["password"])
    
    before = s.get(r.url("dashboard"), timeout=r.config["timeout"], allow_redirects=False)
    s.get(r.url("logout"), timeout=r.config["timeout"], allow_redirects=False)
    after = s.get(r.url("dashboard"), timeout=r.config["timeout"], allow_redirects=False)
    
    assert after.status_code in r.config["expected_status"]["unauthenticated"], (
        f"Tras logout, dashboard sigue accesible (status={after.status_code})"
    )
    return True, f"Logout OK: antes={before.status_code}, después={after.status_code}"

# ==============================================================================
# SEGURIDAD TRANSVERSAL
# ==============================================================================

@test("Seguridad", "SGS-23", "Cabeceras de seguridad HTTP", "Seguridad transversal")
def t_security_headers(r: TestRunner):
    resp = requests.get(r.url("login_page"), timeout=r.config["timeout"])
    headers = {k.lower(): v for k, v in resp.headers.items()}
    recommended = ["x-frame-options", "x-content-type-options"]
    missing = [h for h in recommended if h not in headers]
    if missing:
        return False, f"Cabeceras ausentes: {missing}"
    return True, "Cabeceras de seguridad presentes"

@test("Seguridad", "SGS-23", "CSRF token en formulario de login", "Seguridad transversal")
def t_csrf_token_present(r: TestRunner):
    resp = requests.get(r.url("login_page"), timeout=r.config["timeout"])
    csrf_field = r.config["fields"]["csrf"]
    has_csrf = csrf_field in resp.text or "csrf" in resp.text.lower()
    if not has_csrf:
        return False, f"No se detectó campo CSRF ('{csrf_field}')"
    return True, "CSRF token detectado"

# ==============================================================================
# MAIN
# ==============================================================================

def main():
    parser = argparse.ArgumentParser(description="Test Suite Sprint 1 — SIGES")
    parser.add_argument("--base-url", default=CONFIG["base_url"], help="URL base")
    parser.add_argument("--verbose", action="store_true", help="Mostrar detalle")
    parser.add_argument("--json", default=None, help="Exportar reporte a JSON")
    parser.add_argument("--only", default=None, help="Ejecutar solo ciertos grupos")
    args = parser.parse_args()

    CONFIG["base_url"] = args.base_url
    only_groups = [g.strip() for g in args.only.split(",")] if args.only else None

    runner = TestRunner(CONFIG, verbose=args.verbose, only_groups=only_groups)
    runner.tests = runner_registry.tests
    runner.run()

    if args.json:
        runner.export_json(args.json)

    failed_or_error = any(r.status in ("FAIL", "ERROR") for r in runner.results)
    sys.exit(1 if failed_or_error else 0)

if __name__ == "__main__":
    main()