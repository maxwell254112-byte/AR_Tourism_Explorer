-- Fix photo card text (remove broken ??? Chinese)
SET NAMES utf8mb4;

UPDATE attractions
SET short_description = 'Bukit Bendera / Penang Hill - panoramic views of George Town.'
WHERE name = 'Penang Hill';

UPDATE attractions
SET short_description = 'Kek Lok Si Temple - major hilltop Buddhist temple in Air Itam.'
WHERE name = 'Kek Lok Si Temple';

UPDATE attractions
SET short_description = 'George Town street art - murals and heritage streets.'
WHERE name = 'George Town Street Art';
