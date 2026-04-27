
-- Reflect & Rise database structure.

CREATE DATABASE IF NOT EXISTS reflect_rise_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reflect_rise_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    email VARCHAR(191) NOT NULL,
    token VARCHAR(128) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS daily_checkins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    mood_label VARCHAR(50) NOT NULL,
    mood_rating TINYINT NOT NULL,
    energy_level TINYINT NOT NULL DEFAULT 3,
    stress_level TINYINT NOT NULL DEFAULT 3,
    sleep_quality TINYINT NOT NULL DEFAULT 3,
    influences TEXT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_daily_checkins_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS reflection_prompts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    prompt_text TEXT NOT NULL,
    difficulty VARCHAR(50) NOT NULL DEFAULT 'Medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reflection_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    prompt_id INT UNSIGNED NOT NULL,
    category VARCHAR(100) NOT NULL,
    prompt_text TEXT NOT NULL,
    response_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reflection_entries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reflection_entries_prompt FOREIGN KEY (prompt_id) REFERENCES reflection_prompts(id) ON DELETE CASCADE
);

INSERT INTO reflection_prompts (category, prompt_text, difficulty)
SELECT * FROM (
    SELECT 'Gratitude', 'What''s one small thing you''re grateful for today, and why did it matter to you?', 'Easy' UNION ALL
    SELECT 'Gratitude', 'Think about someone who supported you recently. What would you want to thank them for?', 'Medium' UNION ALL
    SELECT 'Stress', 'How would you describe your stress level this week, and what''s contributing to it?', 'Medium' UNION ALL
    SELECT 'Stress', 'What signs tell you that stress is starting to build up for you?', 'Medium' UNION ALL
    SELECT 'Workload', 'Which task is taking the most mental space right now, and why?', 'Medium' UNION ALL
    SELECT 'Workload', 'What would make your workload feel more manageable this week?', 'Medium' UNION ALL
    SELECT 'Motivation', 'What has motivated you recently, even in a small way?', 'Easy' UNION ALL
    SELECT 'Motivation', 'What usually helps you regain focus when your motivation drops?', 'Medium' UNION ALL
    SELECT 'Self Care', 'What is one kind thing you can do for yourself today?', 'Easy' UNION ALL
    SELECT 'Self Care', 'When do you feel most rested, and how can you create more of that?', 'Medium' UNION ALL
    SELECT 'Growth', 'What is something you handled better this month than you would have before?', 'Medium' UNION ALL
    SELECT 'Growth', 'What have you learned about yourself lately?', 'Medium' UNION ALL
    SELECT 'Relationships', 'What is one boundary you need to set this week to protect your energy?', 'Medium' UNION ALL
    SELECT 'Relationships', 'Who makes you feel supported, and how can you lean into that support?', 'Easy'
) AS seed_prompts
WHERE NOT EXISTS (SELECT 1 FROM reflection_prompts);


CREATE TABLE IF NOT EXISTS journal_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'Free Write',
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_journal_entries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS goals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'Mental Health',
    status VARCHAR(50) NOT NULL DEFAULT 'Active',
    target_value INT NOT NULL DEFAULT 1,
    current_value INT NOT NULL DEFAULT 0,
    unit VARCHAR(50) NOT NULL DEFAULT 'days',
    due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
