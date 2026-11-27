import subprocess, sys, json, numpy as np, wave
src, plan, total, out = sys.argv[1], json.loads(sys.argv[2]), float(sys.argv[3]), sys.argv[4]
SR = 44100
raw = subprocess.run(['ffmpeg','-loglevel','error','-i',src,'-ac','1','-ar',str(SR),'-f','s16le','-'], capture_output=True, check=True).stdout
a = np.frombuffer(raw, np.int16).astype(np.float32) / 32768
track = np.zeros(int(total*SR), np.float32); prev = 0
for cs, ce, at in plan:
    seg = a[int(cs*SR):int(ce*SR)].copy()
    f = int(0.03*SR); seg[:f] *= np.linspace(0,1,f); seg[-f:] *= np.linspace(1,0,f)
    p = max(int(at*SR), prev + int(0.1*SR))
    seg = seg[:len(track)-p]; track[p:p+len(seg)] += seg; prev = p+len(seg)
    print(f'{cs:5.2f}-{ce:5.2f} -> {p/SR:5.2f}-{prev/SR:5.2f}')
track /= np.abs(track).max()/0.95
w = wave.open(out,'wb'); w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR); w.writeframes((track*32767).astype(np.int16).tobytes()); w.close()
