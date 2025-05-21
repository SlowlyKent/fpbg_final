CREATE TABLE IF NOT EXISTS products (
    product_id VARCHAR(50) PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    brand VARCHAR(255) NOT NULL,
    stock_quantity DECIMAL(10,3) NOT NULL DEFAULT 0,
    unit_of_measure VARCHAR(50) NOT NULL,
    category VARCHAR(100) NOT NULL,
    cost_price DECIMAL(10, 2) NOT NULL,
    selling_price DECIMAL(10, 2) NOT NULL,
    stock_status VARCHAR(50) NOT NULL,
    expiration_date DATE NOT NULL
);
