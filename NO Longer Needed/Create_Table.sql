-- Create Table products_location 

CREATE TABLE IF NOT EXISTS products_location (
  products_id int(11) NOT NULL AUTO_INCREMENT,
  products_location varchar(10) NOT NULL,
  PRIMARY KEY (products_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3567;

-- Create triger to automatically insert new rows on insert to products table

DELIMITER //
CREATE TRIGGER zen_product_location_after_ins_trig AFTER INSERT ON products
 FOR EACH ROW BEGIN

  INSERT INTO zen_products_location (products_id, products_location) VALUES (new.products_id,'');

END
//
DELIMITER ;

-- Update table with all existing products

INSERT INTO products_location (products_id, products_location) 
SELECT products_id,"" FROM products;