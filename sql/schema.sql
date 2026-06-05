CREATE TABLE IF NOT EXISTS users (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  email         TEXT    NOT NULL UNIQUE,
  password_hash TEXT    NOT NULL,
  role          TEXT    NOT NULL CHECK (role IN ('citizen', 'staff', 'decision_maker', 'admin')),
  full_name     TEXT    NOT NULL,
  created_at    TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS waste_categories (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  code        TEXT NOT NULL UNIQUE,
  name        TEXT NOT NULL,
  description TEXT
);

CREATE TABLE IF NOT EXISTS cities (
  id       INTEGER PRIMARY KEY AUTOINCREMENT,
  locality TEXT NOT NULL UNIQUE,
  lat      REAL,
  lng      REAL
);

CREATE TABLE IF NOT EXISTS accumulation_reports (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  reporter_id     INTEGER NOT NULL,
  city_id INTEGER NOT NULL,
  category_id     INTEGER,
  description     TEXT    NOT NULL,
  status          TEXT    NOT NULL DEFAULT 'open'
                          CHECK (status IN ('open', 'assigned', 'resolved', 'rejected')),
  severity        INTEGER NOT NULL DEFAULT 2 CHECK (severity BETWEEN 1 AND 3),
  lat             REAL,
  lng             REAL,
  created_at      TEXT    NOT NULL DEFAULT (datetime('now')),
  resolved_at     TEXT,
  FOREIGN KEY (reporter_id)     REFERENCES users (id),
  FOREIGN KEY (city_id) REFERENCES cities (id),
  FOREIGN KEY (category_id)     REFERENCES waste_categories (id)
);

CREATE TABLE IF NOT EXISTS collection_events (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  city_id INTEGER NOT NULL,
  category_id     INTEGER NOT NULL,
  staff_id        INTEGER NOT NULL,
  quantity_kg     REAL    NOT NULL DEFAULT 0,
  collected_at    TEXT    NOT NULL DEFAULT (datetime('now')),
  notes           TEXT,
  FOREIGN KEY (city_id) REFERENCES cities (id),
  FOREIGN KEY (category_id)     REFERENCES waste_categories (id),
  FOREIGN KEY (staff_id)        REFERENCES users (id)
);

CREATE TABLE IF NOT EXISTS cleanup_logs (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  report_id INTEGER NOT NULL,
  staff_id  INTEGER NOT NULL,
  action    TEXT    NOT NULL,
  note      TEXT,
  logged_at TEXT    NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (report_id) REFERENCES accumulation_reports (id),
  FOREIGN KEY (staff_id)  REFERENCES users (id)
);

CREATE INDEX IF NOT EXISTS idx_reports_city ON accumulation_reports (city_id);
CREATE INDEX IF NOT EXISTS idx_reports_created      ON accumulation_reports (created_at);
CREATE INDEX IF NOT EXISTS idx_reports_status       ON accumulation_reports (status);
CREATE INDEX IF NOT EXISTS idx_collections_hood     ON collection_events (city_id);
CREATE INDEX IF NOT EXISTS idx_cleanup_report       ON cleanup_logs (report_id);
