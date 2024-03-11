import os
import sys

def configure(a,b):
    file = open('/etc/wpa_supplicant/wpa_supplicant.conf', 'w')
    file.write('ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev\nupdate_config=1\ncountry=US\n\nnetwork={\n        ssid="' + a + '"\n        psk="' + b + '"\n}')
    file.close()
    os.system('wpa_cli -i wlan0 reconfigure')
    
if __name__ == "__main__":
    a = sys.argv[1]
    b = sys.argv[2]
    configure(a,b)