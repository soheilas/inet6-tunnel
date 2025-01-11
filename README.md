# Auto Tunnel and x-ui Setup Script 🚀

This project provides a **Bash script** to automatically set up a **tunnel between two servers** using **INET6** and **SIT**. Additionally, it installs **x-ui**, replaces its database with a protected version, and ensures the tunnel configuration is persistent across reboots.

---

## 🛠 Features
- Automated IPv6 tunnel setup between a local and external server
- **x-ui** installation and secure database replacement
- Password-protected database download to ensure security
- Persistent configuration setup for both servers


---

## ⚙️ How It Works
The script performs the following steps:
1. **Sets up the IPv6 tunnel** between two servers
2. Installs **x-ui** on the external server
3. **Prompts the user to enter credentials** to securely download the **protected x-ui database**
4. **Restarts the x-ui service**
5. Ensures the tunnel setup is **persistent** on both servers

---

## 🚀 How to Use
### 1️⃣ Clone the Repository
```bash
git clone https://github.com/yourusername/Auto-Tunnel-xui.git
cd Auto-Tunnel-xui
```

### 1️⃣ Run the Script
```bash
sudo bash auto_tunnel_xui.sh
```

### 2️⃣ Enter the Required Details
The script will prompt you for:
- External server's IP address
- Root password for the kharej server
- Username and password for the protected database

### 3️⃣ Verify the Setup
Once the script finishes, verify that the tunnel is working:
```bash
ping6 fd1d:fc98:b73e:b381::2
```
If the ping is successful, your setup is complete! ✅

---

## 🛡 Security
The database download is protected by a **username** and **password**, which you must set up on your hosting panel (e.g., DirectAdmin). The script will prompt for these credentials during execution.


