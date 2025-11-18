CREATE VIEW `sellers` AS
SELECT `users`.`id` AS `sellerId`
FROM `users`
WHERE `users`.`role` = 'seller';
