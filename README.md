# Epoint.az Payment Gateway for WHMCS

## 📥 Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/bzbaxram/EpointGatewayForWHMCS.git
   ```

2. **Navigate to the project directory:**

   ```bash
   cd epoint-whmcs
   ```

3. **Copy the file from `gateways` folder to your WHMCS server.**
4. **Copy the callback file from `callback` folder to your WHMCS server.**
5. **Permissions:**
   - Ensure PHP scripts have execution permissions.
   - Check the permissions for the `modules/gateways/epoint.php` directory.
   - Check the permissions for the `modules/gateways/callback/epoint.php` directory.

## 🚀 Configuration

1. Log in to the WHMCS admin panel.
2. Go to `Settings` → `Payments` → `Payment Gateways`.
3. Select `Epoint.az` from the available gateways.
4. Enter **Public Key**, **Private Key**, **Success URL**, **Error URL**.
5. Save the settings.

## 🔐 Enabling CORS (if required)

If you encounter CORS issues, add the following to your Apache server configuration:

```apache
<Directory /var/www/whmcs>
     Header always set Access-Control-Allow-Origin "https://example.com"
     Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS"
     Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
</Directory>
```
