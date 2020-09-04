CREATE TABLE IF NOT EXISTS swag_payment_sezzle_settings_general (
    `id`                        INT(11)      UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                   INT(11)      NOT NULL,
    `active`                    TINYINT(1),
    `merchant_uuid`             VARCHAR(255),
    `public_key`                VARCHAR(255),
    `private_key`               VARCHAR(255),
    `sandbox`                   TINYINT(1),
    `payment_action`            VARCHAR(255),
    `log_level`                 INT(11)      NOT NULL,
    `display_errors`            TINYINT(1)   NOT NULL,
    `merchant_location`         VARCHAR(255) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;
