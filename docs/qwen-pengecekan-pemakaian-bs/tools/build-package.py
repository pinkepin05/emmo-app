"""Build a Qwen context bundle from first-party source; never mutate runtime code."""

from __future__ import annotations

import hashlib
import json
import re
import shutil
import sys
import zipfile
from datetime import datetime, timezone
from pathlib import Path


PACKAGE = Path(__file__).resolve().parents[1]
ROOT = Path(sys.argv[1]).resolve() if len(sys.argv) > 1 else PACKAGE.parents[1]
SOURCE = PACKAGE / "source"
CHAT = PACKAGE / "kode-untuk-chat"
ARCHIVE = PACKAGE.parent / "qwen-pengecekan-pemakaian-bs.zip"

if ROOT not in PACKAGE.parents or not (ROOT / "composer.json").is_file():
    raise SystemExit("Package must be inside the intended Laravel workspace.")
for target in (SOURCE, CHAT, ARCHIVE):
    if ROOT not in target.resolve().parents:
        raise SystemExit("Output escaped the workspace.")


def sha256(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def write(path: Path, value: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(value, encoding="utf-8", newline="\n")


selected: set[str] = set()
for directory in ("app", "resources", "routes", "config", "tests", "database/migrations", "database/seeders", "database/factories"):
    for path in (ROOT / directory).rglob("*"):
        if path.is_file() and path.suffix.lower() in {".php", ".js", ".css", ".json", ".md"}:
            selected.add(path.relative_to(ROOT).as_posix())
for path in (ROOT / "docs").glob("*.md"):
    selected.add(path.relative_to(ROOT).as_posix())
for path in (ROOT / "public/js").rglob("*.js"):
    selected.add(path.relative_to(ROOT).as_posix())
selected.update([
    "bootstrap/app.php", "bootstrap/providers.php", "public/index.php",
    "composer.json", "composer.lock", "package.json", "vite.config.js",
    "phpunit.xml", "artisan", ".env.example", ".editorconfig", "README.md",
])

contents: dict[str, str] = {}
manifest: list[dict] = []
secret_pattern = re.compile(r"(?:sk-proj-[A-Za-z0-9_-]{20,}|ghp_[A-Za-z0-9]{20,}|-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----)")
for relative in sorted(selected):
    original = ROOT / relative
    if not original.is_file():
        raise SystemExit(f"Required source missing: {relative}")
    data = original.read_bytes()
    content = data.decode("utf-8")
    if secret_pattern.search(content):
        raise SystemExit(f"Review possible embedded credential before packaging: {relative}")
    destination = SOURCE / relative
    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(original, destination)
    assert destination.read_bytes() == data, relative
    contents[relative] = content
    manifest.append({"path": relative, "bytes": len(data), "sha256": sha256(data)})

schema = json.loads((PACKAGE / "SCHEMA_DATABASE.json").read_text(encoding="utf-8-sig"))
schema_lines = [
    "# Skema database lokal aktual", "",
    f"Waktu pembacaan (UTC): {schema['generated_at_utc']}. Driver: `{schema['driver']}`.", "",
    "Hanya metadata, tanpa isi transaksi/akun. Bukan migrasi fitur baru atau dump yang dapat langsung diimpor. Kolom/FK/index berasal dari database, bukan dugaan dari model.", "",
    "Nama schema/database tujuan FK dikeluarkan; nama tabel dan kolom tetap disertakan.", "",
]


def cell(value: object) -> str:
    return str(value).replace("|", "\\|").replace("\n", " ").replace("\r", " ")


for table, metadata in schema["tables"].items():
    schema_lines.extend([f"## {table}", ""])
    if not metadata["exists"]:
        schema_lines.extend(["Tidak ditemukan pada database saat pembacaan.", ""])
        continue
    schema_lines.extend(["| Kolom | Tipe aktual | Nullable | Auto increment | Default |", "| --- | --- | --- | --- | --- |"])
    for column in metadata["columns"]:
        schema_lines.append(f"| {cell(column['name'])} | {cell(column['type'])} | {'Ya' if column['nullable'] else 'Tidak'} | {'Ya' if column['auto_increment'] else 'Tidak'} | {cell(column.get('default'))} |")
    schema_lines.extend(["", "Indeks:", ""])
    for index in metadata["indexes"]:
        kind = "PRIMARY" if index["primary"] else ("UNIQUE" if index["unique"] else "INDEX")
        schema_lines.append(f"- `{index['name']}`: {kind} ({', '.join(index['columns'])}).")
    schema_lines.extend(["", "Foreign key:", ""])
    if not metadata["foreign_keys"]:
        schema_lines.append("- Tidak ada FK fisik yang tercatat.")
    for fk in metadata["foreign_keys"]:
        schema_lines.append(f"- `{fk['name']}`: ({', '.join(fk['columns'])}) → `{fk['foreign_table']}` ({', '.join(fk['foreign_columns'])}); update {fk['on_update']}, delete {fk['on_delete']}.")
    schema_lines.append("")
write(PACKAGE / "SCHEMA_DATABASE.md", "\n".join(schema_lines))

core = {
    "composer.json", "phpunit.xml", "bootstrap/app.php", "bootstrap/providers.php", "routes/web.php",
    "app/Models/User.php", "app/Models/Role.php", "app/Models/CostCategory.php", "app/Models/Periode.php",
    "app/Models/TransaksiOperasionalHeader.php", "app/Models/TransaksiOperasionalDetail.php",
    "app/Models/TransaksiPerjalananDinasHeader.php", "app/Models/TransaksiPerjalananDinasDetail.php",
    "app/Models/Concerns/UsesBsBalance.php", "app/Models/ApprovalBsFinance.php", "app/Models/PengajuanBs.php",
    "app/Support/AppTime.php", "app/Support/Money.php", "app/Services/DocumentNumberService.php",
    "app/Services/PeriodService.php", "app/Providers/AppServiceProvider.php", "config/permission.php",
    "config/bs.php", "database/seeders/PermissionSeeder.php",
}
controllers = {
    f"app/Http/Controllers/{name}.php" for name in (
        "TransaksiOperasionalController", "TransaksiPerjalananDinasController", "ApprovalFinanceController",
        "BsUsageApprovalController", "LaporanBsController", "BsBalanceController", "AccessManagementController",
    )
}


def group(relative: str) -> int:
    if relative in core:
        return 1
    if relative in controllers:
        return 2
    if relative.startswith(("app/Services/", "app/Http/Middleware/", "app/Observers/", "app/Events/", "app/Listeners/")):
        return 3
    if relative.startswith(("resources/views/transaksi_operasional/", "resources/views/transaksi_perjalanan_dinas/", "resources/views/approval-finance/", "resources/views/bs-usage-approvals/")):
        return 4
    if relative.startswith(("resources/views/layouts/", "resources/views/components/", "resources/views/partials/", "resources/views/dashboard/", "resources/views/laporan-bs/", "resources/views/bs-balance/", "resources/views/admin/", "public/js/")):
        return 5
    if relative.startswith("database/"):
        return 6
    if relative.startswith("tests/"):
        return 7
    if relative.startswith("docs/"):
        return 8
    return 9


labels = {
    1: "inti-model-route-penomoran-izin", 2: "controller-transaksi-finance-laporan",
    3: "layanan-saldo-middleware", 4: "tampilan-transaksi-dan-finance",
    5: "layout-menu-laporan-aset", 6: "migrasi-seeder-factory",
    7: "pengujian-existing", 8: "dokumentasi-existing", 9: "kode-pendukung-lain",
}
chunks: list[dict] = []
entries_by_path = {entry["path"]: entry for entry in manifest}


def emit(group_id: int, part: int, paths: list[str]) -> None:
    name = f"{group_id:02d}-{labels[group_id]}-{part:02d}.md"
    output = [f"# Kode existing: {labels[group_id]}, bagian {part}", "", "Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.", ""]
    for relative in paths:
        content = contents[relative]
        runs = [len(match[0]) for match in re.finditer(r"~{3,}", content)]
        fence = "~" * max(4, max(runs, default=0) + 1)
        suffix = Path(relative).suffix
        language = {".php": "php", ".js": "javascript", ".css": "css", ".json": "json", ".lock": "json", ".xml": "xml", ".md": "markdown"}.get(suffix, "text")
        output.extend([f"## FILE: {relative}", "", f"SHA-256: `{entries_by_path[relative]['sha256']}`", "", fence + language, content, fence, ""])
        entries_by_path[relative]["chat_file"] = f"kode-untuk-chat/{name}"
    write(CHAT / name, "\n".join(output))
    chunks.append({"file": f"kode-untuk-chat/{name}", "group": group_id, "paths": paths, "bytes": (CHAT / name).stat().st_size})


for group_id in labels:
    paths = [relative for relative in sorted(selected) if group(relative) == group_id]
    current: list[str] = []
    size = 0
    part = 1
    for relative in paths:
        length = entries_by_path[relative]["bytes"]
        if current and size + length > 180_000:
            emit(group_id, part, current)
            current, size, part = [], 0, part + 1
        current.append(relative)
        size += length
    if current:
        emit(group_id, part, current)

index_lines = [
    "# Indeks kode lengkap untuk Qwen", "",
    f"{len(manifest)} file sumber, {sum(entry['bytes'] for entry in manifest):,} byte, dibagi menjadi {len(chunks)} bagian Markdown.", "",
    "Mulai dengan dokumen 01–05, SCHEMA_DATABASE.md dan kelompok 01 (inti). Lanjutkan kelompok 02–06 untuk implementasi, 07 untuk tes, 08 untuk sejarah aturan bisnis. Kelompok 09 menyediakan dependensi kode pendukung agar konteks tidak terputus.", "",
    "Setiap file disertakan utuh dan hanya muncul sekali dalam bagian kode. File besar dapat membuat satu bagian melebihi target 180 KB; jangan memotong isinya tanpa memberi Qwen file lengkap. Upload bertahap bila kapasitas konteks terbatas.", "",
]
for chunk in chunks:
    index_lines.extend([f"## [{Path(chunk['file']).name}]({chunk['file']})", "", f"Ukuran UTF-8: {chunk['bytes']:,} byte. Isi:", ""])
    index_lines.extend(f"- [`{relative}`](source/{relative})" for relative in chunk["paths"])
    index_lines.append("")
write(PACKAGE / "INDEKS_KODE.md", "\n".join(index_lines))

manifest_data = {
    "generated_at_utc": datetime.now(timezone.utc).isoformat(),
    "purpose": "Existing application source and implementation instructions for Qwen; no new feature implementation.",
    "source_file_count": len(manifest), "source_total_bytes": sum(entry["bytes"] for entry in manifest),
    "schema_table_count": len(schema["tables"]), "chat_part_count": len(chunks),
    "excluded": [".env", "vendor", "node_modules", "database rows/dumps", "storage", "bootstrap/cache", ".git", "private backups", "user uploads"],
    "files": manifest,
}
write(PACKAGE / "MANIFEST.json", json.dumps(manifest_data, ensure_ascii=False, indent=2) + "\n")

for entry in manifest:
    expected = entry["sha256"]
    assert sha256((ROOT / entry["path"]).read_bytes()) == expected, f"Runtime source changed: {entry['path']}"
    assert sha256((SOURCE / entry["path"]).read_bytes()) == expected, f"Copy differs: {entry['path']}"
    markdown = (PACKAGE / entry["chat_file"]).read_text(encoding="utf-8")
    assert contents[entry["path"]].replace("\r\n", "\n") in markdown, f"Chat copy incomplete: {entry['path']}"
actual_source = {path.relative_to(SOURCE).as_posix() for path in SOURCE.rglob("*") if path.is_file()}
assert actual_source == selected, "Unexpected/missing source files."
actual_chat = {path.relative_to(PACKAGE).as_posix() for path in CHAT.glob("*.md")}
assert actual_chat == {chunk["file"] for chunk in chunks}, "Unexpected chat files."

report = {
    "verified_at_utc": datetime.now(timezone.utc).isoformat(),
    "source_files": len(manifest), "schema_tables": len(schema["tables"]), "chat_parts": len(chunks),
    "all_source_copies_sha256_match": True, "all_original_source_files_unchanged": True,
    "all_source_files_present_in_chat_parts_in_full": True,
    "unexpected_source_or_chat_files": False,
    "actual_env_or_business_data_included": False,
    "new_feature_implemented": False, "application_tests_run_for_packaging": False,
    "zip_check": "See adjacent qwen-pengecekan-pemakaian-bs.zip.sha256.json for archive checksum and entry verification.",
}
write(PACKAGE / "VERIFIKASI_PAKET.json", json.dumps(report, ensure_ascii=False, indent=2) + "\n")

package_files = sorted(path for path in PACKAGE.rglob("*") if path.is_file() and "__pycache__" not in path.parts)
with zipfile.ZipFile(ARCHIVE, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
    for path in package_files:
        archive.write(path, arcname=f"{PACKAGE.name}/{path.relative_to(PACKAGE).as_posix()}")

with zipfile.ZipFile(ARCHIVE) as archive:
    assert archive.testzip() is None, "ZIP CRC failure."
    expected_names = {f"{PACKAGE.name}/{path.relative_to(PACKAGE).as_posix()}" for path in package_files}
    assert len(archive.namelist()) == len(expected_names)
    assert set(archive.namelist()) == expected_names
    for path in package_files:
        name = f"{PACKAGE.name}/{path.relative_to(PACKAGE).as_posix()}"
        assert archive.read(name) == path.read_bytes(), name

receipt = {
    "archive": ARCHIVE.name, "sha256": sha256(ARCHIVE.read_bytes()), "bytes": ARCHIVE.stat().st_size,
    "entries": len(package_files), "crc_ok": True, "all_entries_match_package": True,
    "source_files": len(manifest), "schema_tables": len(schema["tables"]), "chat_parts": len(chunks),
}
write(ARCHIVE.with_suffix(ARCHIVE.suffix + ".sha256.json"), json.dumps(receipt, indent=2) + "\n")
print(json.dumps(receipt, indent=2))
