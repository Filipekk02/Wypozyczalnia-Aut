

-- Tabela users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `is_admin`) VALUES
('Admin', 'System', 'admin@wypozyczalnia.pl', '$2y$10$Uo0B4UfM5tq5iFJv4zJlduA5VWx5/EhRHEa2RBxgRYY6K/Q8K8T4i', 1);

-- Tabela cars
DROP TABLE IF EXISTS `cars`;
CREATE TABLE `cars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `engine` VARCHAR(100),
  `fuel` VARCHAR(50),
  `power` INT,
  `daily_price` INT NOT NULL DEFAULT 200,
  `gearbox` VARCHAR(50),
  `year` INT,
  `image` VARCHAR(255),
  `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cars` (`brand`, `model`, `engine`, `fuel`, `power`, `daily_price`, `gearbox`, `year`, `image`, `description`) VALUES
('Audi', 'A4', '2.0 TDI', 'Diesel', 190, 300, 'Manual', 2021, 'audi_a4.jpg', 'Komfortowy sedan klasy premium.'),
('BMW', '320i', '2.0 Turbo', 'Benzyna', 184, 280, 'Automatic', 2020, 'bmw_320i.jpg', 'Dynamiczne auto klasy średniej z napędem na tył.'),
('Volkswagen', 'Golf 8', '1.5 TSI', 'Benzyna', 150, 250, 'Manual', 2022, 'golf8.jpg', 'Popularny hatchback o świetnych osiągach.'),
('Mercedes', 'C200', '2.0', 'Benzyna', 204, 320, 'Automatic', 2021, 'mercedes_c200.jpg', 'Luksusowy sedan z nowoczesnym wnętrzem.'),
('Toyota', 'Corolla', '1.8 Hybrid', 'Hybryda', 122, 200, 'Automatic', 2022, 'toyota_corolla.jpg', 'Ekonomiczny samochód miejski z napędem hybrydowym.');

-- Tabela bookings
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `car_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `extras` TEXT,
  `pickup_location` VARCHAR(255) NOT NULL,
  `return_location` VARCHAR(255) NOT NULL,
  `status` ENUM('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela extras
DROP TABLE IF EXISTS `extras`;
CREATE TABLE `extras` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `price` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `extras` (`name`, `price`) VALUES
('Fotelik', 50),
('Bagażnik dachowy', 30),
('Nawigacja GPS', 40);
