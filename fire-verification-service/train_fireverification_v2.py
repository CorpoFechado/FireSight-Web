# %% [1] Install & align dependencies cleanly without conflicts
!pip install -q --upgrade "protobuf>=5.29.1,<6.0.0" tf2onnx onnxruntime

# %% [2] Mount Google Drive
import os
import zipfile
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from google.colab import drive

drive.mount('/content/drive')

# %% [3] Unzip dataset
# Update ZIP_PATH if your zip file is named differently in firesight_v2
ZIP_PATH = "/content/drive/MyDrive/firesight_v2/firesight dataset.zip"
EXTRACT_PATH = "/content/dataset"

if not os.path.exists(EXTRACT_PATH):
    with zipfile.ZipFile(ZIP_PATH, 'r') as zip_ref:
        zip_ref.extractall(EXTRACT_PATH)
    print("Dataset extracted successfully.")
else:
    print("Dataset already extracted.")

# Resolve nested folder extraction automatically
if os.path.exists(os.path.join(EXTRACT_PATH, "firesight dataset")):
    DATASET_ROOT = os.path.join(EXTRACT_PATH, "firesight dataset")
else:
    DATASET_ROOT = EXTRACT_PATH

print("Contents of dataset root:", os.listdir(DATASET_ROOT))  # Should display ['train', 'val', 'test']

# %% [4] Import TensorFlow & Machine Learning Libraries
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
from sklearn.metrics import (
    classification_report, confusion_matrix, roc_auc_score,
    precision_score, recall_score, f1_score, accuracy_score
)

print("TensorFlow version:", tf.__version__)
print("GPU available:", tf.config.list_physical_devices('GPU'))

# %% [5] Configuration
IMG_SIZE = (224, 224)
BATCH_SIZE = 32
SEED = 42
AUTOTUNE = tf.data.AUTOTUNE

TRAIN_DIR = os.path.join(DATASET_ROOT, "train")
VAL_DIR = os.path.join(DATASET_ROOT, "val")
TEST_DIR = os.path.join(DATASET_ROOT, "test")

# Save Directory Setup
SAVE_DIR = "/content/drive/MyDrive/firesight_v2"
os.makedirs(SAVE_DIR, exist_ok=True)

# %% [6] Load datasets from directory
train_ds = keras.utils.image_dataset_from_directory(
    TRAIN_DIR,
    labels="inferred",
    label_mode="binary",
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    shuffle=True,
    seed=SEED,
)

val_ds = keras.utils.image_dataset_from_directory(
    VAL_DIR,
    labels="inferred",
    label_mode="binary",
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    shuffle=False,
)

test_ds = keras.utils.image_dataset_from_directory(
    TEST_DIR,
    labels="inferred",
    label_mode="binary",
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    shuffle=False,
)

class_names = train_ds.class_names
print("Class names (0/1 order):", class_names)

assert class_names == ["fire", "non_fire"], (
    f"Unexpected class order {class_names} — folder names may have changed."
)

POSITIVE_CLASS = "fire"

# Invert labels: 'fire' (0) -> 1 (Positive), 'non_fire' (1) -> 0 (Negative)
def flip_label(x, y):
    return x, 1.0 - y

train_ds = train_ds.map(flip_label, num_parallel_calls=AUTOTUNE)
val_ds = val_ds.map(flip_label, num_parallel_calls=AUTOTUNE)
test_ds = test_ds.map(flip_label, num_parallel_calls=AUTOTUNE)

FLIPPED_CLASS_NAMES = ["non_fire", "fire"]

# %% [7] Data Augmentation
data_augmentation = keras.Sequential([
    layers.RandomFlip("horizontal"),
    layers.RandomRotation(0.1),
    layers.RandomBrightness(0.15),
    layers.RandomContrast(0.15),
    layers.RandomZoom(0.1),
], name="data_augmentation")

# %% [8] Preprocessing Pipeline
preprocess_input = keras.applications.mobilenet_v3.preprocess_input

def prepare(ds, augment=False):
    if augment:
        ds = ds.map(lambda x, y: (data_augmentation(x, training=True), y),
                    num_parallel_calls=AUTOTUNE)
    ds = ds.map(lambda x, y: (preprocess_input(x), y), num_parallel_calls=AUTOTUNE)
    return ds.prefetch(AUTOTUNE)

train_ds_prepped = prepare(train_ds, augment=True)
val_ds_prepped = prepare(val_ds, augment=False)
test_ds_prepped = prepare(test_ds, augment=False)

# %% [9] Build Architecture: MobileNetV3Large
base_model = keras.applications.MobileNetV3Large(
    input_shape=IMG_SIZE + (3,),
    include_top=False,
    weights="imagenet",
)
base_model.trainable = False  # Freeze backbone for initial head training

inputs = keras.Input(shape=IMG_SIZE + (3,))
x = base_model(inputs, training=False)
x = layers.GlobalAveragePooling2D()(x)
x = layers.Dropout(0.3)(x)
x = layers.Dense(64, activation="relu")(x)
x = layers.Dropout(0.2)(x)
outputs = layers.Dense(1, activation="sigmoid")(x)

model = keras.Model(inputs, outputs, name="firesight_mobilenetv3")
model.summary()

# %% [10] Compile (Stage 1: Frozen Backbone)
model.compile(
    optimizer=keras.optimizers.Adam(learning_rate=1e-3),
    loss="binary_crossentropy",
    metrics=[
        "accuracy",
        keras.metrics.Precision(name="precision"),
        keras.metrics.Recall(name="recall"),
        keras.metrics.AUC(name="auc"),
    ],
)

# %% [11] Callbacks (Stage 1)
callbacks_stage1 = [
    keras.callbacks.EarlyStopping(
        monitor="val_loss", patience=5, restore_best_weights=True
    ),
    keras.callbacks.ModelCheckpoint(
        os.path.join(SAVE_DIR, "best_model_stage1.keras"),
        monitor="val_loss", save_best_only=True
    ),
    keras.callbacks.ReduceLROnPlateau(
        monitor="val_loss", factor=0.5, patience=3, min_lr=1e-6
    ),
]

# %% [12] Train Stage 1
EPOCHS_STAGE1 = 20

history1 = model.fit(
    train_ds_prepped,
    validation_data=val_ds_prepped,
    epochs=EPOCHS_STAGE1,
    callbacks=callbacks_stage1,
)

# %% [13] Stage 2: Unfreeze Backbone for Fine-Tuning
base_model.trainable = True

# Freeze lower layers, keep top 30 layers trainable
fine_tune_at = len(base_model.layers) - 30
for layer in base_model.layers[:fine_tune_at]:
    layer.trainable = False

model.compile(
    optimizer=keras.optimizers.Adam(learning_rate=1e-5),
    loss="binary_crossentropy",
    metrics=[
        "accuracy",
        keras.metrics.Precision(name="precision"),
        keras.metrics.Recall(name="recall"),
        keras.metrics.AUC(name="auc"),
    ],
)

callbacks_stage2 = [
    keras.callbacks.EarlyStopping(
        monitor="val_loss", patience=5, restore_best_weights=True
    ),
    keras.callbacks.ModelCheckpoint(
        os.path.join(SAVE_DIR, "best_model_final.keras"),
        monitor="val_loss", save_best_only=True
    ),
    keras.callbacks.ReduceLROnPlateau(
        monitor="val_loss", factor=0.5, patience=3, min_lr=1e-7
    ),
]

EPOCHS_STAGE2 = 15

history2 = model.fit(
    train_ds_prepped,
    validation_data=val_ds_prepped,
    epochs=EPOCHS_STAGE2,
    callbacks=callbacks_stage2,
)

# %% [14] Plot Training History
def plot_history(h1, h2, metric):
    combined = h1.history[metric] + h2.history[metric]
    combined_val = h1.history[f"val_{metric}"] + h2.history[f"val_{metric}"]
    plt.figure(figsize=(8, 4))
    plt.plot(combined, label=f"train_{metric}")
    plt.plot(combined_val, label=f"val_{metric}")
    plt.axvline(x=len(h1.history[metric]), color="gray", linestyle="--", label="fine-tune start")
    plt.title(f"Training and Validation {metric.capitalize()}")
    plt.xlabel("Epoch")
    plt.legend()
    plt.grid(True)
    plt.show()

plot_history(history1, history2, "accuracy")
plot_history(history1, history2, "loss")

# %% [15] Test Set Evaluation
test_loss, test_acc, test_prec, test_rec, test_auc = model.evaluate(test_ds_prepped)
print(f"\nTest Evaluation Summary:")
print(f"Accuracy:  {test_acc:.4f}")
print(f"Precision: {test_prec:.4f}")
print(f"Recall:    {test_rec:.4f}")
print(f"AUC:       {test_auc:.4f}")

# %% [16] Detailed Classification Metrics & Confusion Matrix
y_true = []
y_pred_prob = []

for images, labels in test_ds_prepped:
    preds = model.predict(images, verbose=0)
    y_true.extend(labels.numpy().flatten().tolist())
    y_pred_prob.extend(preds.flatten().tolist())

y_true = np.array(y_true)
y_pred_prob = np.array(y_pred_prob)
y_pred = (y_pred_prob >= 0.5).astype(int)

print("\nClassification Report:")
print(classification_report(y_true, y_pred, target_names=FLIPPED_CLASS_NAMES))

cm = confusion_matrix(y_true, y_pred)
plt.figure(figsize=(5, 4))
sns.heatmap(cm, annot=True, fmt="d", cmap="Blues",
            xticklabels=FLIPPED_CLASS_NAMES, yticklabels=FLIPPED_CLASS_NAMES)
plt.xlabel("Predicted")
plt.ylabel("Actual")
plt.title("Confusion Matrix (Test Set)")
plt.show()

print(f"\nAccuracy:  {accuracy_score(y_true, y_pred):.4f}")
print(f"Precision: {precision_score(y_true, y_pred):.4f}")
print(f"Recall:    {recall_score(y_true, y_pred):.4f}")
print(f"F1-score:  {f1_score(y_true, y_pred):.4f}")
print(f"ROC-AUC:   {roc_auc_score(y_true, y_pred_prob):.4f}")

# %% [17] Save Master Keras Models
model.save(os.path.join(SAVE_DIR, "fire_model_final.h5"))
model.save(os.path.join(SAVE_DIR, "fire_model_final.keras"))
print(f"Master models saved to {SAVE_DIR} (.h5 and .keras).")

# %% [18] Convert to ONNX
import tf2onnx
import onnxruntime as ort

ONNX_PATH = os.path.join(SAVE_DIR, "fire_model_final.onnx")

input_signature = [tf.TensorSpec([None, *IMG_SIZE, 3], tf.float32, name="input")]
onnx_model, _ = tf2onnx.convert.from_keras(
    model, input_signature=input_signature, opset=13
)

with open(ONNX_PATH, "wb") as f:
    f.write(onnx_model.SerializeToString())

print(f"ONNX model saved successfully to: {ONNX_PATH}")

# %% [19] Sanity Check: Compare Keras vs. ONNX Predictions
sample_images, sample_labels = next(iter(test_ds_prepped))

# Keras prediction
keras_preds = model.predict(sample_images, verbose=0).flatten()

# ONNX prediction
ort_session = ort.InferenceSession(ONNX_PATH, providers=["CPUExecutionProvider"])
onnx_input_name = ort_session.get_inputs()[0].name
onnx_preds = ort_session.run(
    None, {onnx_input_name: sample_images.numpy().astype(np.float32)}
)[0].flatten()

max_diff = np.max(np.abs(keras_preds - onnx_preds))
print(f"\nMax prediction difference (Keras vs ONNX): {max_diff:.8f}")

if max_diff < 1e-4:
    print("PASS: ONNX model predictions match Keras closely. Safe for deployment.")
else:
    print("WARNING: Predictions differ more than expected. Inspect before deploying.")

print("\nSample comparison (first 5):")
for i in range(min(5, len(keras_preds))):
    print(f"  true={int(sample_labels[i].numpy())}  "
          f"keras={keras_preds[i]:.4f}  onnx={onnx_preds[i]:.4f}")