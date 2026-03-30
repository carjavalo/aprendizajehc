-- Script SQL para crear la tabla areas manualmente
-- Ejecutar en phpMyAdmin o cliente MySQL

-- 1. Verificar si la tabla existe y eliminarla si es necesario
DROP TABLE IF EXISTS `areas`;

-- 2. Crear la tabla areas con la estructura correcta
CREATE TABLE `areas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_categoria` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `areas_cod_categoria_index` (`cod_categoria`),
  KEY `areas_descripcion_index` (`descripcion`),
  CONSTRAINT `areas_cod_categoria_foreign` FOREIGN KEY (`cod_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Insertar algunos datos de prueba (opcional)
INSERT INTO `areas` (`descripcion`, `cod_categoria`, `created_at`, `updated_at`) VALUES
('Área de Prueba 1', 1, NOW(), NOW()),
('Área de Prueba 2', 1, NOW(), NOW()),
('Área de Prueba 3', 2, NOW(), NOW());

-- 4. Registrar la migración en la tabla migrations
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2025_06_19_200000_create_areas_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));

-- 5. Verificar la estructura creada
DESCRIBE `areas`;

-- 6. Mostrar datos insertados
SELECT * FROM `areas`;

-- Comentarios:
-- - Asegúrate de que la tabla 'categorias' exista antes de ejecutar este script
-- - Ajusta los valores de cod_categoria según las categorías existentes en tu sistema
-- - Este script creará la tabla con todas las columnas necesarias para el sistema de áreas
