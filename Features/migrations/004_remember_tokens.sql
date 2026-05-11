-- Remember-me tokens table
CREATE TABLE IF NOT EXISTS remember_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  selector VARCHAR(64) NOT NULL,
  token_hash VARCHAR(128) NOT NULL,
  expires DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(selector),
  INDEX(user_id),
  CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
);
