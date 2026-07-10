#!/usr/bin/env python3
"""
Merge payment/tax keys from local .env into the server .env via FTP.

Usage (PowerShell):
  $env:FTP_PASSWORD = "your-password"
  python scripts/push_server_env.py

Then on server (SSH or hosting panel):
  php artisan config:clear
  php artisan cache:clear
"""

from __future__ import annotations

import os
import re
import sys
from ftplib import FTP
from pathlib import Path

HOST = os.environ.get("FTP_HOST", "49.12.85.100")
USER = os.environ.get("FTP_USER", "dainelylab-dev")
PASSWORD = os.environ.get("FTP_PASSWORD", "")
ROOT = Path(__file__).resolve().parents[1]
HTDOCS = "htdocs"
REMOTE_ENV = ".env"

KEYS = [
    "SQUARE_APPLICATION_ID",
    "SQUARE_ACCESS_TOKEN",
    "SQUARE_LOCATION_ID",
    "SQUARE_ENVIRONMENT",
    "SQUARE_VERIFY_SSL",
    "SQUARE_CHARGE_CURRENCY",
    "SHOPIFY_TAX_ENABLED",
    "SHOPIFY_TAX_FALLBACK",
]


def parse_env(text: str) -> dict[str, str]:
    out: dict[str, str] = {}
    for line in text.splitlines():
        if not line.strip() or line.lstrip().startswith("#"):
            continue
        if "=" not in line:
            continue
        key, value = line.split("=", 1)
        out[key.strip()] = value.strip()
    return out


def render_env(values: dict[str, str], original: str) -> str:
    lines = original.splitlines()
    seen: set[str] = set()
    updated: list[str] = []

    for line in lines:
        if "=" in line and not line.lstrip().startswith("#"):
            key = line.split("=", 1)[0].strip()
            if key in values:
                updated.append(f"{key}={values[key]}")
                seen.add(key)
                continue
        updated.append(line)

    missing = [k for k in values if k not in seen]
    if missing:
        if updated and updated[-1].strip():
            updated.append("")
        updated.append("# --- synced by push_server_env.py ---")
        for key in missing:
            updated.append(f"{key}={values[key]}")

    return "\n".join(updated).rstrip() + "\n"


def main() -> int:
    if not PASSWORD:
        print("ERROR: set FTP_PASSWORD", file=sys.stderr)
        return 1

    local_env = ROOT / ".env"
    if not local_env.is_file():
        print("ERROR: local .env not found", file=sys.stderr)
        return 1

    local_values = parse_env(local_env.read_text(encoding="utf-8"))
    patch = {k: local_values[k] for k in KEYS if k in local_values and local_values[k] != ""}
    if not patch:
        print("ERROR: no Square/tax keys found in local .env", file=sys.stderr)
        return 1

    ftp = FTP(HOST, timeout=60)
    ftp.login(USER, PASSWORD)
    ftp.cwd(HTDOCS)

    remote_text = ""
    try:
        chunks: list[bytes] = []
        ftp.retrbinary(f"RETR {REMOTE_ENV}", chunks.append)
        remote_text = b"".join(chunks).decode("utf-8", errors="replace")
    except Exception:
        remote_text = ""

    merged = render_env(patch, remote_text or "")
    ftp.storbinary(f"STOR {REMOTE_ENV}", merged.encode("utf-8"))
    print(f"Updated server {REMOTE_ENV}")

    for cache_file in ("bootstrap/cache/config.php", "bootstrap/cache/services.php"):
        try:
            ftp.delete(cache_file)
            print(f"  deleted {cache_file} (forces fresh .env read)")
        except Exception:
            pass

    ftp.quit()

    print("\nSynced keys:")
    for key in patch:
        print(f"  - {key}")
    print("\nOptional on server SSH: php artisan config:clear && php artisan view:clear")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
