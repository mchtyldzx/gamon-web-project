CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('citizen', 'staff', 'decision_maker')),
  full_name TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS waste_categories (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  description TEXT
);

CREATE TABLE IF NOT EXISTS neighborhoods (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  locality TEXT NOT NULL,
  UNIQUE (name, locality)
);

CREATE TABLE IF NOT EXISTS accumulation_reports (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  reporter_id INTEGER NOT NULL,
  neighborhood_id INTEGER NOT NULL,
  category_id INTEGER,
  description TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'open' CHECK (status IN ('open', 'assigned', 'resolved', 'rejected')),
  severity INTEGER NOT NULL DEFAULT 2 CHECK (severity BETWEEN 1 AND 3),
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  resolved_at TEXT,
  FOREIGN KEY (reporter_id) REFERENCES users (id),
  FOREIGN KEY (neighborhood_id) REFERENCES neighborhoods (id),
  FOREIGN KEY (category_id) REFERENCES waste_categories (id)
);

CREATE INDEX IF NOT EXISTS idx_reports_neighborhood ON accumulation_reports (neighborhood_id);
CREATE INDEX IF NOT EXISTS idx_reports_created ON accumulation_reports (created_at);
