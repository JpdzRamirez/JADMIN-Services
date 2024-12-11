--Primer script
ALTER TABLE icon_produccion.servicio_pasajeros
ADD COLUMN sub_cuenta VARCHAR(10) NULL,
ADD COLUMN affe VARCHAR(15) NULL,
ADD COLUMN solicitado VARCHAR(20) NULL,
ADD COLUMN autorizado VARCHAR(20) NULL;

--Segundo Script
UPDATE icon_produccion.servicio_pasajeros
SET sub_cuenta = 391,
    affe = 560000,
    solicitado = 'Herneley Gualteros',
    autorizado = 'Cesar Romero';


