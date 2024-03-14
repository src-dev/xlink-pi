import os
import sys

def configure(params):
    with open('/etc/wpa_supplicant/wpa_supplicant.conf', 'r') as file: contents = file.read().split('network=')[0]
    contents += 'network={\n'
    first = True
    for param in params:
        if not first: contents += '\n'
        contents += '        ' + param
        first = False
    contents += '\n}'
    with open('/etc/wpa_supplicant/wpa_supplicant.conf', 'w') as file: file.write(contents)
    os.system('wpa_cli -i wlan0 reconfigure')
    
if __name__ == "__main__": configure(sys.argv[1:])
    
    