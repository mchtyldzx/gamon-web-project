INSERT OR IGNORE INTO waste_categories (code, name, description) VALUES
  ('household', 'Household', 'Mixed municipal waste from homes and small businesses.'),
  ('paper', 'Paper & cardboard', 'Newspapers, packaging cardboard, office paper.'),
  ('plastic', 'Plastic', 'Packaging, bottles, and other recyclable plastics.'),
  ('glass', 'Glass', 'Bottles and jars (sorted by color where required).'),
  ('metal', 'Metal', 'Cans, scrap metal, and similar materials.'),
  ('organic', 'Organic / bio', 'Food and garden waste suitable for composting.'),
  ('hazardous', 'Hazardous', 'Batteries, chemicals, medical sharps — special handling.');

INSERT OR IGNORE INTO cities (locality, lat, lng) VALUES
  ('Iași', 47.1783, 27.5621),
  ('București', 44.3850, 26.0720),
  ('Cluj-Napoca', 46.7640, 23.6830),
  ('Timișoara', 45.7570, 21.2580),
  ('Constanța', 44.1640, 28.5950);
