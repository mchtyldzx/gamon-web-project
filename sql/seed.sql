INSERT OR IGNORE INTO waste_categories (code, name, description) VALUES
  ('household', 'Household', 'Mixed municipal waste from homes and small businesses.'),
  ('paper', 'Paper & cardboard', 'Newspapers, packaging cardboard, office paper.'),
  ('plastic', 'Plastic', 'Packaging, bottles, and other recyclable plastics.'),
  ('glass', 'Glass', 'Bottles and jars (sorted by color where required).'),
  ('metal', 'Metal', 'Cans, scrap metal, and similar materials.'),
  ('organic', 'Organic / bio', 'Food and garden waste suitable for composting.'),
  ('hazardous', 'Hazardous', 'Batteries, chemicals, medical sharps — special handling.');

INSERT OR IGNORE INTO neighborhoods (name, locality) VALUES
  ('Riverbank', 'Example City'),
  ('Old Town', 'Example City'),
  ('Industrial East', 'Example City'),
  ('Hillcrest', 'Example City'),
  ('Lakeside', 'Example City');
