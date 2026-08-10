#!/usr/bin/env python3
"""Verify live Admin for pdo_pgsql / Supabase banners via HTTPS + IP resolve."""

from __future__ import annotations

import re
import subprocess
import sys
from pathlib import Path
from urllib.parse import urlencode

ROOT = Path(__file__).resolve().parents[1]
COOKIE = ROOT / "storage" / "framework" / "live_admin_cookies.txt"
RESOLVE = "dev.dainelylab.com:443:49.12.85.100"
BASE = "https://dev.dainelylab.com"


def load_env() -> dict[str, str]:
    env: dict[str, str] = {}
    path = ROOT / ".env"
    if not path.is_file():
        return env
    for line in path.read_text(encoding="utf-8", errors="ignore").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        k, _, v = line.partition("=")
        env[k.strip()] = v.strip().strip('"').strip("'")
    return env


def curl(args: list[str], data: str | None = None) -> tuple[int, str]:
    cmd = [
        "curl.exe",
        "-sk",
        "--resolve",
        RESOLVE,
        "-c",
        str(COOKIE),
        "-b",
        str(COOKIE),
        "-L",
        "--max-time",
        "60",
        *args,
    ]
    if data is not None:
        cmd += ["-X", "POST", "--data-raw", data]
    p = subprocess.run(cmd, capture_output=True, text=True, encoding="utf-8", errors="ignore")
    return p.returncode, p.stdout or ""


def flags_for(html: str) -> list[str]:
    flat = re.sub(r"<[^>]+>", " ", html)
    flat = re.sub(r"\s+", " ", flat)
    needles = [
        "pdo_pgsql",
        "Ask hosting",
        "Supabase database connection failed",
        "Internal links manager offline",
        "Welcome back",
        "Internal Knowledge Graph",
        "Synced Products",
        "Create Content Link",
        "Admin Login",
    ]
    return [n for n in needles if n.lower() in flat.lower()]


def main() -> int:
    env = load_env()
    email, password = env.get("ADMIN_EMAIL", ""), env.get("ADMIN_PASSWORD", "")
    print("creds_set:", bool(email and password))
    if not email or not password:
        print("ERROR: ADMIN_EMAIL / ADMIN_PASSWORD missing in .env")
        return 1

    if COOKIE.exists():
        COOKIE.unlink()

    code, login_html = curl([BASE + "/admin/login"])
    print("login_get:", code, "len", len(login_html))
    m = re.search(r'name="_token"[^>]*value="([^"]+)"', login_html)
    token = m.group(1) if m else ""
    print("csrf:", bool(token))
    if not token:
        print("ERROR: no CSRF token — cannot login")
        return 1

    payload = urlencode({"_token": token, "email": email, "password": password})
    code, _ = curl([BASE + "/admin/login"], data=payload)
    print("login_post:", code)

    for path in ["/admin/dashboard", "/admin/related"]:
        code, html = curl([BASE + path])
        flags = flags_for(html)
        print(path, "len="+str(len(html)), "flags:", ", ".join(flags) if flags else "NONE_OK")
        flat = re.sub(r"\s+", " ", re.sub(r"<[^>]+>", " ", html))
        m2 = re.search(r"Synced Products\s+(\d+)", flat)
        if m2:
            print("  products_metric:", m2.group(1))

    for path in ["/health/supabase", "/ext-check.php"]:
        code, html = curl([BASE + path])
        snippet = html[:180].replace("\n", " ")
        print(path, "len="+str(len(html)), "snippet:", snippet)

    if COOKIE.exists():
        COOKIE.unlink(missing_ok=True)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
