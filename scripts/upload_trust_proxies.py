#!/usr/bin/env python3
"""Upload missing TrustProxies middleware to live (fixes HTTP 500 on dainely.com)."""

from __future__ import annotations

import os
import sys
from ftplib import FTP, error_perm
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
HOST = os.environ.get("FTP_HOST", "49.12.85.100")
USER = os.environ.get("FTP_USER", "dainelylab-dev")
PASSWORD = os.environ.get("FTP_PASSWORD", "")
HTDOCS = "htdocs"
_HOME = ""

FILES = [
    ("app/Http/Middleware/TrustProxies.php", "app/Http/Middleware/TrustProxies.php"),
    ("app/Http/Middleware/TrackPageViews.php", "app/Http/Middleware/TrackPageViews.php"),
    ("app/Http/Middleware/SetCloudflareCacheHeaders.php", "app/Http/Middleware/SetCloudflareCacheHeaders.php"),
]


def load_ftp_password() -> str:
    if PASSWORD:
        return PASSWORD
    env_path = ROOT / ".env"
    if not env_path.is_file():
        return ""
    for line in env_path.read_text(encoding="utf-8", errors="ignore").splitlines():
        line = line.strip()
        if line.startswith("FTP_PASSWORD="):
            return line.split("=", 1)[1].strip().strip('"').strip("'")
    return ""


def goto_htdocs(ftp: FTP) -> None:
    ftp.cwd(_HOME)
    ftp.cwd(HTDOCS)


def ensure_remote_dir(ftp: FTP, remote_dir: str) -> None:
    goto_htdocs(ftp)
    for part in [p for p in remote_dir.split("/") if p]:
        try:
            ftp.cwd(part)
        except error_perm:
            ftp.mkd(part)
            ftp.cwd(part)


def upload_file(ftp: FTP, local: Path, remote_rel: str) -> None:
    remote_dir, filename = remote_rel.rsplit("/", 1)
    ensure_remote_dir(ftp, remote_dir)
    with local.open("rb") as fh:
        ftp.storbinary(f"STOR {filename}", fh)
    print(f"  uploaded {remote_rel}")


def main() -> int:
    password = load_ftp_password()
    if not password:
        print("ERROR: set FTP_PASSWORD", file=sys.stderr)
        return 1

    ftp = FTP(HOST, timeout=60)
    ftp.login(USER, password)
    global _HOME
    _HOME = ftp.pwd()

    for local_rel, remote_rel in FILES:
        local = ROOT / local_rel
        if not local.is_file():
            print(f"ERROR: missing {local}", file=sys.stderr)
            return 1
        print(f"\n=== {remote_rel} ===")
        upload_file(ftp, local, remote_rel)

    ftp.quit()
    print("\nDone. Clear cache on server: php artisan config:clear && php artisan route:clear")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
