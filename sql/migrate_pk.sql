DELIMITER //

CREATE PROCEDURE import_data_by_partition()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE partitionName VARCHAR(64);

  -- Curseur pour récupérer le nom des partitions de la table dans le schéma concerné
  DECLARE cur CURSOR FOR
    SELECT PARTITION_NAME
    FROM INFORMATION_SCHEMA.PARTITIONS
    WHERE TABLE_SCHEMA = 'pmacontrol'  -- Remplacez par le nom de votre schéma
      AND TABLE_NAME = 'ts_value_general_int'
    ORDER BY PARTITION_ORDINAL_POSITION;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO partitionName;
    IF done THEN
      LEAVE read_loop;
    END IF;
    
    -- Ici, la commande d'import. Par exemple, insérer les données de la partition dans une table destination.
    SET @sql = CONCAT('INSERT INTO ts_value_general_int3 SELECT * FROM ts_value_general_int PARTITION (', partitionName, ')');
    
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END LOOP;

  CLOSE cur;
END //

DELIMITER ;

