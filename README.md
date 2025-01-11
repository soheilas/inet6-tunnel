# Auto Tunnel and x-ui Setup Script 🚀

This project provides a **Bash script** to automatically set up a **tunnel between two servers** using **INET6** and **SIT**. Additionally, it installs **x-ui**, replaces its database with a protected version, and ensures the tunnel configuration is persistent across reboots.

---

## 🛠 Features
- Automated IPv6 tunnel setup between a iran and kharej server
- **x-ui** installation and secure database replacement
- Password-protected database download to ensure security
- Persistent configuration setup for both servers


---

## ⚙️ How It Works
The script performs the following steps:
1. **Sets up the IPv6 tunnel** between two servers
2. Installs **x-ui** on kharej server
3. **Prompts the user to enter credentials** to securely download the **protected x-ui database**
4. **Restarts the x-ui service**
5. Ensures the tunnel setup is **persistent** on both servers

---

## 🚀 How to Use

### 1️⃣ Run the Script
```bash
wget -O inet6.sh yun.ir/inet6 && bash inet6.sh
```

### 2️⃣ Enter the Required Details
The script will prompt you for:
- kharej server IP address
- Root password for kharej server
- Username and password for the protected database

### 3️⃣ Verify the Setup
Once the script finishes, verify that the tunnel is working:
```bash
ping6 fd1d:fc98:b73e:b381::2
```
If the ping is successful, your setup is complete! ✅

---
