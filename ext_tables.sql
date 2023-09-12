

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
    debug text DEFAULT '' NOT NULL,
    message text DEFAULT '' NOT NULL,
    message_original text DEFAULT '' NOT NULL,
    original_message text DEFAULT '' NOT NULL,
    envelope_original text DEFAULT '' NOT NULL,
    email_serialized text DEFAULT '' NOT NULL,
    settings text DEFAULT '' NOT NULL
);