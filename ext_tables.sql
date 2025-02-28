

CREATE TABLE tx_sentmail_mail (
    from_name varchar(255) DEFAULT '' NOT NULL,
    from_email varchar(255) DEFAULT '' NOT NULL,
    receiver text DEFAULT '' NOT NULL,
    sender text DEFAULT '' NOT NULL,
    bcc text DEFAULT '' NOT NULL,
    cc text DEFAULT '' NOT NULL,

    subject varchar(255) DEFAULT '' NOT NULL,
    message_id varchar(255) DEFAULT '' NOT NULL,
    internal_id varchar(255) DEFAULT '' NOT NULL,
    is_sent tinyint(1) DEFAULT '0' NOT NULL,
    debug longtext DEFAULT '' NOT NULL,
    message longtext DEFAULT '' NOT NULL,
    message_original longtext DEFAULT '' NOT NULL,
    original_message longtext DEFAULT '' NOT NULL,
    envelope_original longblob DEFAULT '' NOT NULL,
    email_serialized longblob DEFAULT '' NOT NULL,
    settings longtext DEFAULT '' NOT NULL
);
