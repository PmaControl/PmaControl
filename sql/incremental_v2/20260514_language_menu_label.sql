SET NAMES utf8mb4;

INSERT INTO `translation_google`
(`key`, `source_language`, `source_text`, `target_language`, `target_text`)
VALUES
('ad08d8df431fbf7dac9ea14274c58d05688f57fe', 'en', 'Languages', 'fr', 'Langues'),
('ad08d8df431fbf7dac9ea14274c58d05688f57fe', 'en', 'Languages', 'ar', 'اللغات'),
('ad08d8df431fbf7dac9ea14274c58d05688f57fe', 'en', 'Languages', 'ru', 'Языки'),
('ad08d8df431fbf7dac9ea14274c58d05688f57fe', 'en', 'Languages', 'pl', 'Języki'),
('ad08d8df431fbf7dac9ea14274c58d05688f57fe', 'en', 'Languages', 'en', 'Languages'),
('ad08d8df431fbf7dac9ea14274c58d05688f57fe', 'en', 'Languages', 'zh-cn', '语言')
ON DUPLICATE KEY UPDATE
    `source_language` = VALUES(`source_language`),
    `source_text` = VALUES(`source_text`),
    `target_text` = VALUES(`target_text`);
