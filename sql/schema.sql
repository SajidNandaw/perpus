-- =======================================
--  DATABASE
-- =======================================
CREATE DATABASE IF NOT EXISTS perpus
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE perpus;

-- =======================================
--  TABLE users
-- =======================================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(255),
  email VARCHAR(255),
  role ENUM('administrator','petugas','peminjam') NOT NULL DEFAULT 'peminjam',
  alamat TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =======================================
--  TABLE buku
-- =======================================
CREATE TABLE buku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(255) NOT NULL,
  penulis VARCHAR(255),
  penerbit VARCHAR(255),
  tahun_terbit INT,
  stok INT DEFAULT 1,
  cover VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =======================================
--  TABLE kategori buku
-- =======================================
CREATE TABLE kategoribuku (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255) NOT NULL
);

-- =======================================
--  RELASI buku - kategori
-- =======================================
CREATE TABLE kategori_buku_relasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buku_id INT NOT NULL,
  kategori_id INT NOT NULL,
  FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE,
  FOREIGN KEY (kategori_id) REFERENCES kategoribuku(id) ON DELETE CASCADE
);

-- =======================================
--  TABLE peminjaman
-- =======================================
CREATE TABLE peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  buku_id INT NOT NULL,
  tanggal_pinjam DATE,
  tanggal_kembali DATE NULL,
  status ENUM('dipinjam','dikembalikan') DEFAULT 'dipinjam',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- =======================================
--  TABLE reviews (TABEL BARU)
-- =======================================
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id INT NOT NULL,
  user_id INT NOT NULL,
  buku_id INT NOT NULL,
  rating INT CHECK (rating BETWEEN 1 AND 5),
  komentar TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- =======================================
--  ADMIN DEFAULT
--  username: admin
--  password: admin
-- =======================================
INSERT INTO users (username, password, nama_lengkap, email, role)
VALUES (
  'admin',
  'admin',
  'Administrator',
  'admin@example.com',
  'administrator'
);

-- =======================================
--  SAMPLE DATA BUKU
-- =======================================
INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok, cover) VALUES
('Belajar PHP', 'Andi', 'Penerbit A', 2020, 3, NULL),
('Pemrograman Web', 'Budi', 'Penerbit B', 2019, 2, NULL);

-- =======================================
--  SAMPLE KATEGORI
-- =======================================
INSERT INTO kategoribuku (nama) VALUES
('Pemrograman'),
('Basis Data'),
('Sastra');

-- =======================================
--  RELASI BUKU - KATEGORI (opsional)
-- =======================================
INSERT INTO kategori_buku_relasi (buku_id, kategori_id) VALUES 
(1, 1),
(2, 1),
(2, 2);
