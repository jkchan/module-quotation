# Magento 2 Quotation Module
This module allows the admin to view quotations stored in the database's quote table.

# Installation

#### **Prerequisites:**
1. **Magento is installed** and properly set up.
2. Ensure you have **Composer** installed on your system. To check, run:
   ```bash
   composer --version
   ```
3. Have the necessary permissions to execute commands on the server.

---

### **Step 1: Access Your Magento Root Directory**
- Open a terminal or SSH into your server.
- Navigate to your Magento root directory where Magento is installed:
  ```bash
  cd /path/to/magento/root
  ```

---

### **Step 2: Require the Module via Composer**
- Run the following command to install the `jkchan/module-quotation`:
  ```bash
  composer require jkchan/module-quotation
  ```

---

### **Step 3: Enable the Module**
- Once the module is installed, enable it using Magento's command-line tool:
  ```bash
  php bin/magento module:enable Sales_Quote
  ```

---

### **Step 4: Run Magento Setup Commands**
- After enabling the module, run the Magento setup commands:
  ```bash
  php bin/magento setup:upgrade
  php bin/magento setup:di:compile
  php bin/magento setup:static-content:deploy
  ```

---

### **Step 5: Clear Magento Cache**
- Finally, clear the Magento cache to ensure the module works correctly:
  ```bash
  php bin/magento cache:clean
  php bin/magento cache:flush
  ```

---

### **Step 7: Verify Installation**
- Check the Magento Admin Panel under **Sales > Quote Management > Quotes**

---

Feel free to ask if you need further assistance!
