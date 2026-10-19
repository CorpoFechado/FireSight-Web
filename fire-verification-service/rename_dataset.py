"""
Batch-renames all images inside a FIRESIGHT-style dataset folder:

    dataset_root/
    ├── train/
    │   ├── fire/
    │   └── non_fire/
    ├── val/
    │   ├── fire/
    │   └── non_fire/
    └── test/
        ├── fire/
        └── non_fire/

Each image becomes: <class>_<split>_0001.<ext>, e.g. fire_train_0001.jpg
This keeps counters separate per split+class so nothing overlaps or collides.

USAGE:
    1. Edit DATASET_ROOT below to point at your "firesight dataset" folder.
    2. Run:  python rename_dataset.py
    3. (Optional) Set DRY_RUN = True first to preview without renaming anything.
"""

import os

# ---- EDIT THIS ----
DATASET_ROOT = r"C:\Users\Clarence\Desktop\FIRESIGHT DATASETS\firesight dataset"
DRY_RUN = True  # set to False once you've checked the preview looks right
# --------------------

SPLITS = ["train", "val", "test"]
CLASSES = ["fire", "non_fire"]
VALID_EXTS = {".jpg", ".jpeg", ".png", ".webp", ".bmp"}


def rename_folder(folder_path, prefix):
    if not os.path.isdir(folder_path):
        print(f"  [skip] Folder not found: {folder_path}")
        return

    files = sorted(
        f for f in os.listdir(folder_path)
        if os.path.splitext(f)[1].lower() in VALID_EXTS
    )

    if not files:
        print(f"  [empty] No images found in: {folder_path}")
        return

    # Two-pass rename avoids collisions if a target name already exists
    # (e.g. re-running the script on already-renamed files).
    temp_names = []
    for idx, filename in enumerate(files, start=1):
        ext = os.path.splitext(filename)[1].lower()
        old_path = os.path.join(folder_path, filename)
        temp_path = os.path.join(folder_path, f"__tmp_{idx}{ext}")
        temp_names.append((temp_path, f"{prefix}_{idx:04d}{ext}"))
        if not DRY_RUN:
            os.rename(old_path, temp_path)

    for temp_path, final_name in temp_names:
        final_path = os.path.join(folder_path, final_name)
        if not DRY_RUN:
            os.rename(temp_path, final_path)

    action = "Would rename" if DRY_RUN else "Renamed"
    print(f"  [ok] {action} {len(files)} files -> {prefix}_0001..{prefix}_{len(files):04d}")


def main():
    print(f"Dataset root: {DATASET_ROOT}")
    print(f"Dry run: {DRY_RUN}\n")

    for split in SPLITS:
        for cls in CLASSES:
            folder = os.path.join(DATASET_ROOT, split, cls)
            prefix = f"{cls}_{split}"
            print(f"Processing {split}/{cls}...")
            rename_folder(folder, prefix)

    if DRY_RUN:
        print("\nDry run complete. Nothing was renamed. Set DRY_RUN = False to actually rename.")
    else:
        print("\nDone. All folders renamed.")


if __name__ == "__main__":
    main()