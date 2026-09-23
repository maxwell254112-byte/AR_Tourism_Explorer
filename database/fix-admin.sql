-- Ensure admin account exists (password: admin123)
-- Select database synergy1_maxwellteh-AR-Tourism-Explorer first, then import.

SET NAMES utf8mb4;

UPDATE users
SET password_hash = '$2y$12$HGatM7vA4GHTgcuODlKK4e5ddS3uMEFfa1h96xIwlxd.uwfpWRely',
    email = 'admin@artourism.local',
    role = 'admin',
    status = 'active'
WHERE username = 'admin';

INSERT INTO users (username, email, password_hash, role, status)
SELECT 'admin', 'admin@artourism.local', '$2y$12$HGatM7vA4GHTgcuODlKK4e5ddS3uMEFfa1h96xIwlxd.uwfpWRely', 'admin', 'active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

DELETE FROM login_attempts;