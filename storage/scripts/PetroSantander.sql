CREATE TABLE `servicio_pasajeros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `servicios_id` int NOT NULL,
  `pasajeros_id` int NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `servicio_pasajeros_ibfk_1` FOREIGN KEY (`pasajeros_id`) REFERENCES `pasajeros` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `servicio_pasajeros_ibfk_2` FOREIGN KEY (`servicios_id`) REFERENCES `servicios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

ALTER TABLE servicios
    ADD COLUMN CONTRATO_VALE BIGINT NULL,
    ADD COLUMN SECUENCIA SMALLINT NULL;