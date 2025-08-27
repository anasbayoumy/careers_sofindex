-- Add custom email body field to jobs table
ALTER TABLE `jobs` ADD COLUMN `custom_email_body` TEXT NULL AFTER `benefits`;

-- Update existing jobs with some sample custom email bodies
UPDATE `jobs` SET `custom_email_body` = NULL WHERE `title` = 'Software Engineer';
UPDATE `jobs` SET `custom_email_body` = NULL WHERE `title` = 'UI/UX Designer';
UPDATE `jobs` SET `custom_email_body` = 'Thank you for your application to our Marketing Specialist position! We\'re excited to review your profile. To help us better understand your qualifications, please answer the following questions:\n\n1. What digital marketing tools and platforms are you most experienced with?\n2. Can you share an example of a successful marketing campaign you\'ve worked on?\n3. How do you approach target audience research and segmentation?\n4. What metrics do you focus on when measuring marketing success?\n\nPlease reply to this email with your answers. We look forward to learning more about your experience!' WHERE `title` = 'Marketing Specialist';
