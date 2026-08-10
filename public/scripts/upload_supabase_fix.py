#!/usr/bin/env python3
"""Upload Supabase/Admin fix files + health check to live FTP. Clears config cache."""

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
    ("app/Support/SupabaseDb.php", "app/Support/SupabaseDb.php"),
    ("app/Http/Middleware/TrackPageViews.php", "app/Http/Middleware/TrackPageViews.php"),
    ("app/Http/Controllers/Frontend/ProductController.php", "app/Http/Controllers/Frontend/ProductController.php"),
    ("app/Http/Controllers/Admin/AdminController.php", "app/Http/Controllers/Admin/AdminController.php"),
    ("app/Http/Controllers/Admin/AdminDashboardController.php", "app/Http/Controllers/Admin/AdminDashboardController.php"),
    ("app/Http/Controllers/Admin/AdminRelatedController.php", "app/Http/Controllers/Admin/AdminRelatedController.php"),
    ("app/Http/Controllers/Admin/AdminProductController.php", "app/Http/Controllers/Admin/AdminProductController.php"),
    ("app/Http/Controllers/Admin/AdminFaqController.php", "app/Http/Controllers/Admin/AdminFaqController.php"),
    ("app/Http/Controllers/Admin/AdminLandingController.php", "app/Http/Controllers/Admin/AdminLandingController.php"),
    ("app/Http/Controllers/Admin/AdminBundleController.php", "app/Http/Controllers/Admin/AdminBundleController.php"),
    ("app/Http/Controllers/Admin/AdminWebhookController.php", "app/Http/Controllers/Admin/AdminWebhookController.php"),
    ("app/Http/Controllers/Admin/AdminSignalController.php", "app/Http/Controllers/Admin/AdminSignalController.php"),
    ("app/Http/Controllers/Admin/AdminEducationController.php", "app/Http/Controllers/Admin/AdminEducationController.php"),
    ("routes/web.php", "routes/web.php"),
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
    if "/" in remote_rel:
        remote_dir, filename = remote_rel.rsplit("/", 1)
        ensure_remote_dir(ftp, remote_dir)
    else:
        goto_htdocs(ftp)
        filename = remote_rel
    with local.open("rb") as fh:
        ftp.storbinary(f"STOR {filename}", fh)
    print(f"  uploaded {remote_rel}")


def delete_if_exists(ftp: FTP, remote_rel: str) -> None:
    try:
        goto_htdocs(ftp)
        if "/" in remote_rel:
            remote_dir, filename = remote_rel.rsplit("/", 1)
            for part in remote_dir.split("/"):
                ftp.cwd(part)
            ftp.delete(filename)
        else:
            ftp.delete(remote_rel)
        print(f"  deleted {remote_rel}")
    except error_perm:
        pass


def main() -> int:
    password = load_ftp_password()
    if not password:
        print("ERROR: FTP_PASSWORD not set (env or .env)", file=sys.stderr)
        return 1

    ftp = FTP(HOST, timeout=60)
    ftp.login(USER, password)
    global _HOME
    _HOME = ftp.pwd()

    for local_rel, remote in FILES:
        local = ROOT / local_rel
        if not local.is_file():
            print(f"MISSING {local}")
            return 1
        print(f"=== {remote} ===")
        upload_file(ftp, local, remote)

    print("\n=== Clear Laravel config cache ===")
    for cache_file in (
        "bootstrap/cache/config.php",
        "bootstrap/cache/routes-v7.php",
        "bootstrap/cache/routes.php",
    ):
        delete_if_exists(ftp, cache_file)

    ftp.quit()
    print("\nDone. Verify:")
    print("  https://dev.dainelylab.com/ext-check.php")
    print("  https://dev.dainelylab.com/health/supabase")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
