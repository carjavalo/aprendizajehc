-- =====================================================
-- SCRIPT SQL PARA REPARAR LA TABLA AREAS
-- Ejecutar en phpMyAdmin o cliente MySQL
-- =====================================================

-- 1. Eliminar tabla areas si existe (para empezar limpio)
DROP TABLE IF EXISTS `areas`;

-- 2. Crear tabla areas con estructura correcta
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

-- 3. Verificar que existen categorías, si no crear algunas
INSERT IGNORE INTO `categorias` (`descripcion`, `created_at`, `updated_at`) VALUES
('Medicina General', NOW(), NOW()),
('Pediatría', NOW(), NOW()),
('Ginecología', NOW(), NOW()),
('Cardiología', NOW(), NOW()),
('Neurología', NOW(), NOW()),
('Dermatología', NOW(), NOW()),
('Oftalmología', NOW(), NOW()),
('Traumatología', NOW(), NOW());

-- 4. Insertar áreas de prueba
INSERT INTO `areas` (`descripcion`, `cod_categoria`, `created_at`, `updated_at`) VALUES
('Consulta Externa', 1, NOW(), NOW()),
('Urgencias', 1, NOW(), NOW()),
('Hospitalización', 2, NOW(), NOW()),
('Cirugía General', 3, NOW(), NOW()),
('Laboratorio Clínico', 4, NOW(), NOW()),
('Radiología', 5, NOW(), NOW()),
('Farmacia', 6, NOW(), NOW()),
('Rehabilitación', 7, NOW(), NOW()),
('Cuidados Intensivos', 8, NOW(), NOW()),
('Quirófano', 1, NOW(), NOW());

-- 5. Registrar migración en tabla migrations
DELETE FROM `migrations` WHERE `migration` = '2025_06_19_200000_create_areas_table';
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2025_06_19_200000_create_areas_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));

-- 6. Verificar estructura creada
DESCRIBE `areas`;

-- 7. Mostrar datos insertados
SELECT 
    a.id,
    a.descripcion AS area,
    c.descripcion AS categoria,
    a.created_at
FROM areas a
JOIN categorias c ON a.cod_categoria = c.id
ORDER BY a.id;

-- 8. Verificar claves foráneas
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = 'areas' 
AND TABLE_SCHEMA = DATABASE()
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- =====================================================
-- INSTRUCCIONES:
-- 1. Copia todo este código
-- 2. Abre phpMyAdmin (http://localhost/phpmyadmin)
-- 3. Selecciona la base de datos de tu proyecto SHC
-- 4. Ve a la pestaña "SQL"
-- 5. Pega este código y ejecuta
-- 6. Verifica que no hay errores
-- 7. Accede a http://127.0.0.1:8000/capacitaciones/areas
-- =====================================================
