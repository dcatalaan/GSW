import os

from flask import Flask, jsonify, request
from sentence_transformers import SentenceTransformer

app = Flask(__name__)

MODEL_NAME = os.environ.get("EMBEDDING_MODEL", "all-MiniLM-L6-v2")
model = SentenceTransformer(MODEL_NAME)


@app.route("/embed", methods=["POST"])
def embed():
    data = request.get_json(force=True, silent=True) or {}
    text = (data.get("text") or "").strip()
    if not text:
        return jsonify({"error": "No text provided"}), 400
    vector = model.encode(text)
    return jsonify({
        "embedding": vector.tolist(),
        "dimensions": len(vector),
        "model": MODEL_NAME,
    })


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "model": MODEL_NAME})


if __name__ == "__main__":
    from gunicorn.app.base import BaseApplication

    class Standalone(BaseApplication):
        def __init__(self):
            self.options = {
                "bind": "0.0.0.0:8000",
                "workers": 1,
                "timeout": 120,
            }
            super().__init__()

        def load_config(self):
            for key, value in self.options.items():
                self.cfg.set(key, value)

        def load(self):
            return app

    Standalone().run()
