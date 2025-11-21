CREATE VIEW `sellers` AS
SELECT `users`.`userId` AS `sellerId`
FROM `users`
WHERE `users`.`userRole` = 'seller';
