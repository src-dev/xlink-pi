import os
import sys
import json

#Overwrites the network(s) in wpa_supplicant.conf
#Accepts JSON network parameters as arguments
def write(args):
    with open('/etc/wpa_supplicant/wpa_supplicant.conf', 'r') as file: contents = file.read().split('network=')[0]
    networks = []
    for arg in args: networks.append(json.loads(arg))
    last = networks[-1]
    for network in networks:
        contents += 'network={\n'
        first = True
        for param in network:
            if not first: contents += '\n'
            contents += '        ' + param + '=' + network[param]
            first = False
        contents += '\n}'
        if network != last: contents += '\n'
    with open('/etc/wpa_supplicant/wpa_supplicant.conf', 'w') as file: file.write(contents)

#Appends a network to the top of wpa_supplicant.conf
#Accepts JSON network parameters as arguments
def append(arg):
    with open('/etc/wpa_supplicant/wpa_supplicant.conf', 'r') as file: contents = file.read().split('network=')[0]
    networks = parse()
    networks.append(json.loads(arg))
    last = networks[-1]
    for network in networks:
        contents += 'network={\n'
        first = True
        for param in network:
            if not first: contents += '\n'
            contents += '        ' + param + '=' + network[param]
            first = False
        contents += '\n}'
        if network != last: contents += '\n'
    with open('/etc/wpa_supplicant/wpa_supplicant.conf', 'w') as file: file.write(contents)

#Parses the networks in wpa_supplicant.conf
#Returns a list of networks as dictionaries
def parse():
    with open('/etc/wpa_supplicant/wpa_supplicant.conf', 'r') as file: contents = file.read().split('network=')[1:]
    networks = []
    for content in contents:
        params = list(map(str.strip, list(filter(None, content.strip()[1:-1].split('\n')))))
        if not params: continue
        network = {}
        for param in params: 
            split = param.strip().split('=', 1)
            network.update({split[0]:split[1]})
        networks.append(network)
    return networks

if __name__ == "__main__":
    if len(sys.argv) == 1: pass
    elif sys.argv[1] == 'w': write(sys.argv[2:])
    #Prints each network as a JSON line
    elif sys.argv[1] == 'p':
        for network in parse(): print(json.dumps(network))
    elif sys.argv[1] == 'a': append(sys.argv[2])
    
    