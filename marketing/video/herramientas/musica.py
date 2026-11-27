import numpy as np, wave, sys
import json
SR=44100; DUR=float(sys.argv[2]); t=np.arange(int(SR*DUR))/SR
out=np.zeros_like(t)
def note(f): return 440*2**((f-69)/12)
# Progresión Am - F - C - G (2.5 s por acorde, ~96 bpm)
chords=[[57,60,64,69],[53,57,60,65],[48,55,60,64],[55,59,62,67]]
L=2.5
for k in range(int(DUR/L)+1):
    ch=chords[k%4]; st=k*L
    m=(t>=st)&(t<st+L+0.8)
    tt=t[m]-st
    env=np.minimum(tt/0.6,1)*np.exp(-np.maximum(tt-L,0)*4)
    for n in ch:
        f=note(n)
        for det in (-0.12,0.12):
            out[m]+=0.035*env*np.sin(2*np.pi*f*(1+det/100)*tt)
    # bajo
    fb=note(ch[0]-12)
    out[m]+=0.08*env*np.sin(2*np.pi*fb*tt)
# pulso grave en cada tiempo (96 bpm) desde el segundo 4
beat=60/96
for bt in np.arange(3.5,DUR-1.5,beat):
    m=(t>=bt)&(t<bt+0.35); tt=t[m]-bt
    out[m]+=0.18*np.exp(-tt*14)*np.sin(2*np.pi*(55+60*np.exp(-tt*30))*tt)
# hi-hat suave en contratiempos desde el 8
rng=np.random.default_rng(1)
for bt in np.arange(7.5+beat/2,DUR-1.5,beat):
    m=(t>=bt)&(t<bt+0.06); tt=t[m]-bt
    nz=rng.standard_normal(m.sum()); nz=np.diff(np.concatenate([[0],nz]))
    out[m]+=0.025*np.exp(-tt*80)*nz
# whoosh en cambios de escena
for sc in json.loads(sys.argv[3]):
    st=sc-0.6; m=(t>=st)&(t<sc+0.2); tt=t[m]-st
    nz=rng.standard_normal(m.sum())
    nz=np.convolve(nz,np.ones(30)/30,mode='same')
    env=np.sin(np.pi*np.clip(tt/0.8,0,1))**2
    out[m]+=0.35*env*nz
# fades
fi=np.minimum(t/1.5,1); fo=np.clip((DUR-t)/2.0,0,1)
out*=fi*fo
out/=np.max(np.abs(out))*1.15
d=(out*32767).astype(np.int16)
w=wave.open(sys.argv[1],'wb'); w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR); w.writeframes(d.tobytes()); w.close()
print('ok')
