-- Splendid Caterery Seed Data
USE splendid_caterery;

-- Demo users (password for all: splendid123)
-- Hash: $2y$10$8K1p/a0dL1Lk8Y5Y5Y5Y5uOQ5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y5Y
INSERT INTO users (uuid, first_name, last_name, email, phone, password_hash, role) VALUES
(UUID(), 'System', 'Admin', 'admin@splendidcaterery.com', '+233240000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(UUID(), 'Sarah', 'Mensah', 'customer@example.com', '+233240000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
(UUID(), 'Kwame', 'Asante', 'events@splendidcaterery.com', '+233240000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'event_manager'),
(UUID(), 'Chef', 'Amara', 'kitchen@splendidcaterery.com', '+233240000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chef'),
(UUID(), 'Kofi', 'Boateng', 'delivery@splendidcaterery.com', '+233240000005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'delivery'),
(UUID(), 'Ama', 'Darko', 'finance@splendidcaterery.com', '+233240000006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'finance');

-- Service Types
INSERT INTO service_types (name, slug, description, icon, image_url, sort_order) VALUES
('Wedding Catering', 'wedding', 'Elegant multi-course dining for your special day with premium presentation and flawless service.', 'bi-heart', 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80', 1),
('Birthday Catering', 'birthday', 'Celebrate milestones with themed menus, custom cakes, and vibrant party platters.', 'bi-balloon', 'https://images.unsplash.com/photo-1464349095431-e9a21285b5ea?w=800&q=80', 2),
('Funeral Catering', 'funeral', 'Dignified, comforting meals prepared with care for memorial gatherings.', 'bi-flower1', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&q=80', 3),
('Corporate Events', 'corporate', 'Professional catering for conferences, meetings, and executive dining.', 'bi-building', 'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=800&q=80', 4),
('Graduation Parties', 'graduation', 'Celebrate academic achievements with crowd-pleasing buffet spreads.', 'bi-mortarboard', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&q=80', 5),
('Baby Shower', 'baby-shower', 'Delicate finger foods, pastel desserts, and themed refreshment stations.', 'bi-gift', 'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?w=800&q=80', 6),
('Outdoor BBQ', 'outdoor-bbq', 'Smoky grills, live cooking stations, and al fresco dining experiences.', 'bi-fire', 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80', 7),
('Private Chef Services', 'private-chef', 'Exclusive in-home chef experiences with personalized menus.', 'bi-person-badge', 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80', 8),
('Buffet Services', 'buffet', 'Self-service spreads with live carving stations and international cuisine.', 'bi-grid-3x3', 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=800&q=80', 9),
('Cocktail Events', 'cocktail', 'Canapés, hors d''oeuvres, and premium beverage pairings for social gatherings.', 'bi-cup-straw', 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7b1e?w=800&q=80', 10),
('Conference Catering', 'conference', 'Break sessions, working lunches, and gala dinners for large delegations.', 'bi-mic', 'https://images.unsplash.com/photo-1475721027869-e379fe578b46?w=800&q=80', 11),
('School Catering', 'school', 'Nutritious, budget-friendly meals for educational institutions and events.', 'bi-book', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&q=80', 12);

-- Packages
INSERT INTO packages (name, slug, tier, description, price_per_guest, min_guests, max_guests, features, image_url, sort_order) VALUES
('Basic Package', 'basic', 'basic', 'Essential catering with 3 menu items, standard setup, and disposable serviceware.', 45.00, 20, 100, '["3 menu selections", "Standard buffet setup", "Disposable serviceware", "2-hour service", "Basic beverages"]', 'https://images.unsplash.com/photo-1555244162-803834f70033?w=800&q=80', 1),
('Silver Package', 'silver', 'silver', 'Enhanced menu with 5 selections, linen table setup, and dedicated server.', 75.00, 30, 200, '["5 menu selections", "Linen table setup", "Dedicated server", "3-hour service", "Juice & soft drinks", "Dessert station"]', 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80', 2),
('Gold Package', 'gold', 'gold', 'Premium 7-item menu, live cooking station, floral centerpieces, and bar service.', 120.00, 50, 350, '["7 menu selections", "Live cooking station", "Floral centerpieces", "4-hour service", "Full bar service", "Premium desserts", "Event coordinator"]', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=80', 3),
('Platinum Package', 'platinum', 'platinum', 'Luxury 10-item tasting menu, champagne toast, custom décor, and full event team.', 185.00, 75, 500, '["10 menu selections", "Champagne toast", "Custom décor", "6-hour service", "Full event team", "Premium open bar", "Photography-ready plating", "VIP guest treatment"]', 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=800&q=80', 4),
('VIP Package', 'vip', 'vip', 'Bespoke culinary experience with celebrity chef, molecular gastronomy, and white-glove service.', 350.00, 10, 150, '["Bespoke menu design", "Celebrity guest chef", "Molecular gastronomy options", "Unlimited service hours", "Premium spirits collection", "Personal event concierge", "Custom cake design", "After-party snacks"]', 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=800&q=80', 5);

-- Menu Categories
INSERT INTO menu_categories (name, slug, description, image_url, sort_order) VALUES
('Starters', 'starters', 'Appetizing beginnings to whet your appetite', 'https://images.unsplash.com/photo-1541529080196-190a2d5562a2?w=600&q=80', 1),
('Main Course', 'main-course', 'Hearty and satisfying centerpiece dishes', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80', 2),
('Desserts', 'desserts', 'Sweet finales crafted by our pastry chefs', 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=600&q=80', 3),
('Local Dishes', 'local-dishes', 'Authentic Ghanaian flavors and traditions', 'https://images.unsplash.com/photo-1604329760661-e71dc1679b97?w=600&q=80', 4),
('Continental Dishes', 'continental', 'European and international cuisine', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80', 5),
('Drinks', 'drinks', 'Refreshing beverages and premium cocktails', 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=600&q=80', 6),
('Snacks', 'snacks', 'Light bites and finger foods', 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=600&q=80', 7),
('Salads', 'salads', 'Fresh, vibrant salad creations', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80', 8),
('BBQ', 'bbq', 'Smoked and grilled specialties', 'https://images.unsplash.com/photo-1529193591184-1d581684126c?w=600&q=80', 9),
('Seafood', 'seafood', 'Ocean-fresh catches prepared to perfection', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80', 10);

-- Menu Items
INSERT INTO menu_items (category_id, name, slug, description, price, image_url, is_vegetarian, is_vegan, is_halal, is_gluten_free, popularity_score) VALUES
(1, 'Jollof Arancini Balls', 'jollof-arancini', 'Crispy rice balls infused with our signature jollof spice blend, served with pepper sauce.', 8.50, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=600&q=80', 1, 0, 1, 0, 92),
(1, 'Prawn Cocktail Shots', 'prawn-cocktail', 'Fresh Atlantic prawns in Marie Rose sauce with avocado mousse.', 12.00, 'https://images.unsplash.com/photo-1565680018434-b7030f949110?w=600&q=80', 0, 0, 0, 1, 88),
(1, 'Spinach & Feta Triangles', 'spinach-feta', 'Golden phyllo parcels with creamy spinach and feta filling.', 7.00, 'https://images.unsplash.com/photo-1606491956689-2ea866884f1f?w=600&q=80', 1, 0, 1, 0, 75),
(2, 'Grilled Tilapia with Banku', 'grilled-tilapia-banku', 'Whole grilled tilapia with fermented corn banku and spicy shito.', 18.00, 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80', 0, 0, 1, 1, 95),
(2, 'Herb-Crusted Lamb Rack', 'lamb-rack', 'New Zealand lamb with rosemary crust, mint jus, and roasted vegetables.', 32.00, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80', 0, 0, 0, 1, 90),
(2, 'Mushroom Risotto', 'mushroom-risotto', 'Creamy Arborio rice with wild mushrooms, truffle oil, and parmesan.', 16.00, 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=600&q=80', 1, 0, 1, 0, 82),
(2, 'Jollof Rice with Chicken', 'jollof-chicken', 'Our award-winning party jollof with tender grilled chicken thighs.', 14.00, 'https://images.unsplash.com/photo-1604329760661-e71dc1679b97?w=600&q=80', 0, 0, 1, 1, 98),
(3, 'Chocolate Lava Cake', 'lava-cake', 'Warm dark chocolate cake with molten center and vanilla ice cream.', 9.00, 'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?w=600&q=80', 1, 0, 1, 0, 94),
(3, 'Tropical Fruit Pavlova', 'fruit-pavlova', 'Crisp meringue nest with whipped cream and seasonal Ghanaian fruits.', 8.50, 'https://images.unsplash.com/photo-1464305798204-6a5cbce1f251?w=600&q=80', 1, 0, 1, 0, 86),
(3, 'Bofrot with Honey Drizzle', 'bofrot-honey', 'Traditional Ghanaian doughnuts with local honey and cinnamon.', 5.00, 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=600&q=80', 1, 1, 1, 0, 80),
(4, 'Waakye Platter', 'waakye-platter', 'Rice and beans with spaghetti, gari, shito, and assorted proteins.', 12.00, 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=600&q=80', 0, 0, 1, 1, 91),
(4, 'Kelewele & Groundnut Stew', 'kelewele-stew', 'Spiced fried plantain with rich groundnut stew and smoked fish.', 10.00, 'https://images.unsplash.com/photo-1606491956689-2ea866884f1f?w=600&q=80', 0, 0, 1, 1, 87),
(5, 'Beef Wellington', 'beef-wellington', 'Prime beef fillet in puff pastry with mushroom duxelles.', 38.00, 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=600&q=80', 0, 0, 0, 0, 89),
(5, 'Salmon en Croûte', 'salmon-croute', 'Scottish salmon wrapped in herb pastry with dill cream sauce.', 28.00, 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600&q=80', 0, 0, 0, 0, 85),
(6, 'Fresh Tropical Smoothies', 'tropical-smoothies', 'Mango, pineapple, and coconut blend with optional protein boost.', 6.00, 'https://images.unsplash.com/photo-1505252585467-126024f93289?w=600&q=80', 1, 1, 1, 1, 78),
(6, 'Chapman Cocktail', 'chapman', 'Nigeria-inspired refreshing mocktail with Angostura bitters.', 7.50, 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=600&q=80', 1, 1, 1, 1, 83),
(7, 'Mini Spring Rolls', 'mini-spring-rolls', 'Crispy vegetable spring rolls with sweet chili dipping sauce.', 6.50, 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=600&q=80', 1, 1, 1, 0, 76),
(8, 'Caesar Salad', 'caesar-salad', 'Crisp romaine, parmesan, croutons, and house-made Caesar dressing.', 9.00, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80', 0, 0, 0, 0, 84),
(8, 'Quinoa Power Bowl', 'quinoa-bowl', 'Tri-color quinoa with roasted vegetables, avocado, and tahini.', 11.00, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80', 1, 1, 1, 1, 79),
(9, 'BBQ Ribs Platter', 'bbq-ribs', 'Slow-smoked pork ribs with house BBQ sauce and coleslaw.', 22.00, 'https://images.unsplash.com/photo-1529193591184-1d581684126c?w=600&q=80', 0, 0, 0, 1, 93),
(9, 'Grilled Suya Skewers', 'suya-skewers', 'Spicy West African beef skewers with onion rings and tomatoes.', 15.00, 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&q=80', 0, 0, 1, 1, 96),
(10, 'Grilled Lobster Tail', 'lobster-tail', 'Butter-basted lobster with garlic herb butter and lemon.', 45.00, 'https://images.unsplash.com/photo-1565680018434-b7030f949110?w=600&q=80', 0, 0, 0, 1, 88),
(10, 'Seafood Paella', 'seafood-paella', 'Spanish rice with mussels, shrimp, calamari, and saffron.', 35.00, 'https://images.unsplash.com/photo-1532636875304-0c89119d9b67?w=600&q=80', 0, 0, 0, 1, 90);

-- Testimonials
INSERT INTO testimonials (customer_name, event_type, quote, rating, image_url, is_featured) VALUES
('The Asante Family', 'Wedding', 'Splendid Caterery made our wedding absolutely magical. The jollof was the talk of the evening!', 5, 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=400&q=80', 1),
('TechHub Ghana', 'Corporate Event', 'Professional, punctual, and the food quality exceeded our expectations for 300 guests.', 5, 'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=400&q=80', 1),
('Grace Ofori', 'Birthday Party', 'My 50th birthday was unforgettable thanks to their Gold Package. Every detail was perfect.', 5, 'https://images.unsplash.com/photo-1464349095431-e9a21285b5ea?w=400&q=80', 1),
('University of Ghana', 'Graduation', 'They handled 500 graduates with ease. Efficient service and delicious food throughout.', 5, 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=400&q=80', 1);

-- Gallery
INSERT INTO gallery (title, description, image_url, category, event_type, is_featured, sort_order) VALUES
('Elegant Wedding Reception', 'Platinum package setup at Labadi Beach Hotel', 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80', 'Weddings', 'wedding', 1, 1),
('Corporate Gala Dinner', 'Gold package for annual awards night', 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80', 'Corporate', 'corporate', 1, 2),
('Birthday Celebration', 'Themed dessert table and buffet spread', 'https://images.unsplash.com/photo-1464349095431-e9a21285b5ea?w=800&q=80', 'Parties', 'birthday', 1, 3),
('Outdoor BBQ Event', 'Live grilling station at garden party', 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80', 'Outdoor', 'outdoor-bbq', 1, 4),
('Cocktail Canapés', 'Premium hors d''oeuvres for networking event', 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7b1e?w=800&q=80', 'Cocktails', 'cocktail', 1, 5),
('Buffet Spread', 'International buffet with live carving', 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=800&q=80', 'Buffet', 'buffet', 1, 6);

-- Promotions
INSERT INTO promotions (code, title, description, discount_type, discount_value, min_order_amount, max_uses, valid_from, valid_until) VALUES
('WELCOME10', 'Welcome Discount', '10% off your first booking', 'percentage', 10.00, 500.00, 100, '2025-01-01', '2026-12-31'),
('GOLD500', 'Gold Package Deal', '₵500 off Gold packages for 100+ guests', 'fixed', 500.00, 5000.00, 50, '2025-06-01', '2026-12-31'),
('SUMMER15', 'Summer Special', '15% off outdoor BBQ events', 'percentage', 15.00, 1000.00, 30, '2025-06-01', '2026-09-30');

-- Inventory
INSERT INTO inventory_items (name, category, unit, current_stock, min_stock_level, unit_cost, supplier) VALUES
('Basmati Rice', 'Grains', 'kg', 150.00, 50.00, 12.50, 'Accra Grains Ltd'),
('Chicken Thighs', 'Protein', 'kg', 80.00, 30.00, 18.00, 'Fresh Farms Ghana'),
('Tomatoes', 'Vegetables', 'kg', 45.00, 20.00, 5.00, 'Kaneshie Market'),
('Palm Oil', 'Oils', 'liters', 60.00, 20.00, 15.00, 'Wilmar Africa'),
('Fresh Prawns', 'Seafood', 'kg', 25.00, 10.00, 85.00, 'Tema Fish Harbour'),
('Dark Chocolate', 'Baking', 'kg', 15.00, 5.00, 45.00, 'Golden Tree Imports'),
('Mixed Herbs', 'Spices', 'kg', 8.00, 3.00, 35.00, 'Spice World GH'),
('Disposable Plates', 'Supplies', 'pack', 200.00, 50.00, 25.00, 'Party Supplies Accra');

-- Website Content
INSERT INTO website_content (page_key, title, content, meta_description) VALUES
('about', 'About Splendid Caterery', 'Founded with a passion for exceptional cuisine and flawless event execution, Splendid Caterery has become Ghana''s premier catering company. Our team of award-winning chefs, experienced event managers, and dedicated service staff work together to create unforgettable dining experiences for weddings, corporate events, and celebrations of every kind.', 'Learn about Splendid Caterery - Ghana premier catering and event management company.'),
('contact', 'Contact Us', 'We would love to hear about your upcoming event. Reach out for a free consultation and custom quotation.', 'Contact Splendid Caterery for catering quotes and bookings.');

-- Availability (next 90 days sample)
INSERT INTO availability_blocks (block_date, max_bookings, current_bookings)
SELECT DATE_ADD(CURDATE(), INTERVAL seq DAY), 3, 0
FROM (
    SELECT 0 AS seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
    SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
    SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
    SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
    SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION
    SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION
    SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION
    SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION
    SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION
    SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION
    SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION
    SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION
    SELECT 60 UNION SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION
    SELECT 65 UNION SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION
    SELECT 70 UNION SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74 UNION
    SELECT 75 UNION SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79 UNION
    SELECT 80 UNION SELECT 81 UNION SELECT 82 UNION SELECT 83 UNION SELECT 84 UNION
    SELECT 85 UNION SELECT 86 UNION SELECT 87 UNION SELECT 88 UNION SELECT 89
) AS days;

-- Sample booking for demo
INSERT INTO bookings (reference_no, customer_id, service_type_id, package_id, event_manager_id, event_type, event_date, event_time, venue, guest_count, subtotal, tax_amount, total_amount, amount_paid, status, payment_status, qr_code)
SELECT 'SC20250001', 2, 1, 3, 3, 'Wedding', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '14:00:00', 'Labadi Beach Hotel, Accra', 150, 18000.00, 2250.00, 20250.00, 6075.00, 'confirmed', 'partial', 'SC-QR-20250001';

INSERT INTO event_checklists (booking_id, title, sort_order) VALUES
(1, 'Confirm final guest count with client', 1),
(1, 'Verify dietary requirements', 2),
(1, 'Kitchen prep schedule finalized', 3),
(1, 'Delivery route confirmed', 4),
(1, 'Setup team briefed', 5);

INSERT INTO kitchen_orders (booking_id, chef_id, menu_item_id, quantity, status) VALUES
(1, 4, 7, 150, 'preparing'),
(1, 4, 4, 150, 'pending'),
(1, 4, 8, 150, 'pending');

INSERT INTO delivery_tasks (booking_id, driver_id, pickup_time, status) VALUES
(1, 5, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'scheduled');

INSERT INTO payments (booking_id, reference_no, amount, method, status, transaction_id) VALUES
(1, 'PAY20250001', 6075.00, 'mtn_momo', 'completed', 'MTN-TXN-987654321');

INSERT INTO invoices (invoice_no, booking_id, customer_id, subtotal, tax_amount, total_amount, amount_paid, status, due_date) VALUES
('INV20250001', 1, 2, 18000.00, 2250.00, 20250.00, 6075.00, 'sent', DATE_ADD(CURDATE(), INTERVAL 7 DAY));

INSERT INTO reviews (booking_id, customer_id, rating, title, comment, is_approved) VALUES
(1, 2, 5, 'Absolutely Perfect!', 'The team exceeded every expectation. Highly recommend for weddings!', 1);
