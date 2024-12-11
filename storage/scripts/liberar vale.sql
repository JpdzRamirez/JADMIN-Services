ALTER TABLE vale_servicios
ADD COLUMN users_id INT NULL AFTER servicios_id,
ADD COLUMN fecha_edicion DATETIME NULL AFTER users_id,        
ADD COLUMN ultima_observacion VARCHAR(125) NULL AFTER fecha_edicion,
ADD CONSTRAINT fk_users_id FOREIGN KEY (users_id) REFERENCES users(id);