import time
import re
import iwlist
import json
import sys

# Sets timeout in seconds
timeout = 30

#Scan and parse iwlist
timeout += time.time()
while True:
    if time.time() > timeout: break
    cells = iwlist.parse(iwlist.scan(interface='wlan0'))
    if cells: break

networks = []
for cell in cells:
    #Remove hidden networks
    essid = cell['essid']
    if not essid or re.fullmatch("(\\\\x00)+", essid): continue
    #if any(n['essid'] == essid for n in networks): continue
    
    #Determine signal strength in bars (1-4)
    percentage = round(int(cell['signal_quality'])/int(cell['signal_total'])*100)
    bars = 4
    if percentage <= 25: bars = 1
    elif percentage >= 26 and percentage <= 50: bars = 2
    elif percentage >= 51 and percentage <= 75: bars = 3
    
    #Normalize frequency band
    frequency = float(cell['frequency'])
    if frequency >= 2.4 and frequency <= 2.484: band = 2.4
    elif frequency >= 5.15 and frequency <= 5.85: band = 5
    elif frequency >= 5.925 and frequency <= 7.125:band = 6
    
    #Remove 6GHz networks
    if band == 6: continue
    
    #Preserve only relevant information
    networks.append({'mac':cell['mac'],'encryption':cell['encryption'],'essid':essid,'frequency':frequency,'band':band,'signal_percentage':percentage,'signal_bars':bars})

#Sort networks by signal percentage
networks = sorted(networks, key=lambda x:x['signal_percentage'], reverse=True)

if len(sys.argv) == 1: 
    #Remove 2.4GHz networks in favor of 5GHz
    for network in networks:
        if network['band'] == 5:
            for n in networks:
                if n['essid'] == network['essid'] and n['band'] == 2.4: networks.remove(n)
    for network in networks: print(json.dumps(network))
elif len(sys.argv) == 2: print(json.dumps(list(filter(lambda x:x['mac'] == sys.argv[1], networks))[0]))
