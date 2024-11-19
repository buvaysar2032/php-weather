<?php

use yii\db\Migration;

/**
 * Class m200922_105803_create_udf_strip_html_function
 */
class m200922_105803_create_udf_strip_html_function extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function up(): void
    {
        $this->execute(<<<SQL
DROP FUNCTION IF EXISTS fnStripTags;
SQL
);
        $this->execute(<<<SQL
CREATE FUNCTION fnStripTags( Dirty longtext CHARSET utf8mb4 )
RETURNS longtext CHARSET utf8mb4
DETERMINISTIC
BEGIN
  DECLARE iStart, iEnd, iLength int;
    WHILE Locate( '<', Dirty ) > 0 And Locate( '>', Dirty, Locate( '<', Dirty )) > 0 DO
      BEGIN
        SET iStart = Locate( '<', Dirty ), iEnd = Locate( '>', Dirty, Locate('<', Dirty ));
        SET iLength = ( iEnd - iStart) + 1;
        IF iLength > 0 THEN
          BEGIN
            SET Dirty = Insert( Dirty, iStart, iLength, '');
          END;
        END IF;
      END;
    END WHILE;
    RETURN Dirty;
END;
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function down(): void
    {
        $this->execute(<<<SQL
DROP FUNCTION IF EXISTS fnStripTags;
SQL
        );
    }
}
