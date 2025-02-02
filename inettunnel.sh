#!/bin/bash

# =======================
# Auto Tunnel and x-ui Setup Script (by Soheil) 🚀
# =======================

# Colors for messages
GREEN='\033[0;32m'
RED='\033[0;31m'
BOLD='\033[1m'
RESET='\033[0m'

# Function to display progress messages step-by-step
echo_step_done() {
  clear
  echo -e "${GREEN}${BOLD}$1 : DONE${RESET}"
  sleep 4
}

# Function to get the local IPv4 address
get_local_ip() {
  hostname -I | awk '{print $1}'
}

# Display initial message
clear
echo -e "${BOLD}Starting Auto Tunnel and x-ui Setup...${RESET}"
sleep 1

# Install necessary packages
echo -e "Installing necessary packages..."
rm /etc/rc.local && apt-get update -y && apt-get install -y net-tools sshpass
if [ $? -ne 0 ]; then
  echo -e "${RED}${BOLD}Error installing packages! Please check.${RESET}"
  exit 1
fi
echo_step_done "Installing necessary packages"

# Disable UFW (Firewall)
echo -e "Disabling firewall..."
ufw disable
if [ $? -eq 0 ]; then
  echo_step_done "Disabling firewall"
else
  echo -e "${RED}${BOLD}Error disabling firewall!${RESET}"
fi

# Ask for the remote server's IP and password
read -p "Enter kharej server IP: " IPKHAJ
read -sp "Enter root password for kharej server: " root_password
clear
echo_step_done "Server details received"

# Get the local server's IPv4 address
LOCAL_IP=$(get_local_ip)

# Connecting to Kharej server and setting up tunnel
sshpass -p "$root_password" ssh -o StrictHostKeyChecking=no root@$IPKHAJ << EOF
ifconfig sit0 down
ifconfig sit1 down
ip tunnel del sit0
ip tunnel del sit1
ifconfig sit0 up
ifconfig sit0 inet6 tunnel ::$LOCAL_IP
ifconfig sit1 up
ifconfig sit1 inet6 add fd1d:fc98:b73e:b381::2/64

# Make tunnel configuration persistent on remote server
rm /etc/rc.local
if ! grep -q "ifconfig sit0 up" /etc/rc.local; then
  cat <<EOR >> /etc/rc.local
#!/bin/bash
ifconfig sit0 up
ifconfig sit0 inet6 tunnel ::$LOCAL_IP
ifconfig sit1 up
ifconfig sit1 inet6 add fd1d:fc98:b73e:b381::2/64
exit 0
EOR
  chmod +x /etc/rc.local
fi

# Step 1: Install x-ui
VERSION=v2.3.0
yes "" | bash <(curl -Ls "https://raw.githubusercontent.com/mhsanaei/3x-ui/$VERSION/install.sh")
if [ $? -ne 0 ]; then
  echo -e "${RED}${BOLD}Error installing x-ui! Please check.${RESET}"
  exit 1
fi


# Step 2: Download and replace the x-ui database
wget -q --no-check-certificate -O /etc/x-ui/x-ui.db https://www.wooda.ir/shellcode/mmdscripts/database.db
if [ $? -eq 0 ]; then
  echo_step_done "x-ui database replaced"
else
  echo -e "${RED}${BOLD}Error downloading x-ui database!${RESET}"
  exit 1
fi

# Step 3: Restart x-ui service
x-ui restart
if [ $? -eq 0 ]; then
  echo_step_done "x-ui service restarted"
else
  echo -e "${RED}${BOLD}Error restarting x-ui service!${RESET}"
  exit 1
fi

EOF

# Local server setup for tunnel
ifconfig sit1 down
ifconfig sit0 down
ip -6 tunnel del sit1
ifconfig sit0 up
ifconfig sit0 inet6 tunnel ::$IPKHAJ
ifconfig sit1 up
ifconfig sit1 inet6 add fd1d:fc98:b73e:b381::1/64
if [ $? -eq 0 ]; then
  echo_step_done "Local tunnel setup complete"
else
  echo -e "${RED}${BOLD}Error setting up local tunnel!${RESET}"
fi

# Make tunnel configuration persistent locally
if ! grep -q "ifconfig sit0 up" /etc/rc.local; then
  cat <<EOL >> /etc/rc.local
#!/bin/bash
ifconfig sit0 up
ifconfig sit0 inet6 tunnel ::$IPKHAJ
ifconfig sit1 up
ifconfig sit1 inet6 add fd1d:fc98:b73e:b381::1/64
exit 0
EOL
  chmod +x /etc/rc.local
  echo_step_done "Tunnel configuration saved in rc.local"
else
  echo -e "${GREEN}${BOLD}Tunnel configuration already exists in rc.local${RESET}"
fi

# Final success message
echo -e "Pinging the kharej server to check tunnel..."
ping6 -c 2 fd1d:fc98:b73e:b381::2
if [ $? -eq 0 ]; then
  echo -e "${GREEN}${BOLD}Ping successful! Everything is OK.${RESET}"
else
  echo -e "${RED}${BOLD}Ping failed! Please check the tunnel setup.${RESET}"
fi

exit 0
