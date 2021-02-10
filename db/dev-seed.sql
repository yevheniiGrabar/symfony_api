SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `jwt_refresh_token` (`id`, `refresh_token`, `username`, `valid`) VALUES
(1, '18718e71b1c1411b395b94979424c7158a6e0c39fd18d9f3d94e76c5938c58749977a4f2d67d7320fe7874f2be2a09c36afc0c6b4271a873a0aaa2f5de92e24c', 'user@email.com', '2020-02-25 11:24:49');

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'user');

INSERT INTO `user` (`id`, `role_id`, `name`, `email`, `password`) VALUES
(1, 1, 'admin', 'admin@email.com', '$2y$13$nYqiTj5R2UQZZDHs1JFF/e53n9LfNG1NLfr/Ji8IORcpy9z0UNdUe'),
(2, 2, 'user', 'user@email.com', '$2y$13$nYqiTj5R2UQZZDHs1JFF/e53n9LfNG1NLfr/Ji8IORcpy9z0UNdUe');

INSERT INTO `post` (`id`, `user_id`, `title`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 'Admin Post title', 'Admin Post Content', '2021-02-10 10:39:47', '2021-02-10 10:39:47'),
(2, 2, 'User Post title', 'User Post Content', '2021-02-10 10:40:17', '2021-02-10 10:40:17');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
