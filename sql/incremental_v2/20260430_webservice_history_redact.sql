UPDATE `webservice_history_main`
SET `password` = '[redacted]'
WHERE `password` <> '[redacted]';

UPDATE `webservice_history_main`
SET `message` = '[redacted legacy payload]'
WHERE `message` REGEXP '"(password|passwd|pwd|secret|token|api[_-]?key|apikey|authorization|private[_-]?key|key_auth)"[[:space:]]*:';
