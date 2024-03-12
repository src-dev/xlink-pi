import os
import sys

def conf1(essid):
    file = open('/etc/wpa_supplicant/wpa_supplicant.conf', 'w')
    file.write('ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev\nupdate_config=1\ncountry=US\n\nnetwork={\n        ssid="' + essid + '"\n        key_mgmt=NONE\n}')
    file.close()

def conf2(essid,psk):
    file = open('/etc/wpa_supplicant/wpa_supplicant.conf', 'w')
    file.write('ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev\nupdate_config=1\ncountry=US\n\nnetwork={\n        ssid="' + essid + '"\n        psk="' + psk + '"\n}')
    file.close()

if __name__ == "__main__":
    if len(sys.argv) == 2: conf1(sys.argv[1])
    elif len(sys.argv) == 3: conf2(sys.argv[1],sys.argv[2])
    
    os.system('wpa_cli -i wlan0 reconfigure')