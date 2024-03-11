import time
import re
import iwlist
import json

# Sets timeout in seconds
timeout = 30
timeout += time.time()

#Scan and parse iwlist
while True:
    if time.time() > timeout: break
    cells = iwlist.parse(iwlist.scan(interface='wlan0'))
    if cells: break

networks = []
for cell in cells:
    #Remove hidden and duplicate networks
    essid = cell['essid']
    if not essid or re.fullmatch("(\\\\x00){2,}", essid): continue
    if any(n['essid'] == essid for n in networks): continue
    
    #Determine signal strength in bars (1-4)
    percentage = round(int(cell['signal_quality'])/int(cell['signal_total'])*100)
    bars = 4
    if percentage <= 25: bars = 1
    elif percentage >= 26 and percentage <= 50: bars = 2
    elif percentage >= 51 and percentage <= 75: bars = 3
    
    #Preserve only relevant information
    networks.append({'mac':cell['mac'],'encryption':cell['encryption'],'essid':cell['essid'],'frequency':cell['frequency'],'signal_percentage':percentage,'signal_bars':bars})
        
#Sort networks by signal percentage
networks = sorted(networks, key=lambda x:x['signal_percentage'], reverse=True)

for network in networks: print(json.dumps(network))