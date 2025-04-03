import sys
import subprocess

def get_gpg_public_key():
    try:
        result = subprocess.run(
            ["gpg", "--armor", "--export"],
            capture_output=True,
            text=True,
            check=True
        )
        return result.stdout
    except:
        return "Erro ao obter a chave pública!"

if __name__ == "__main__":
    if len(sys.argv) != 1:
        print("Uso: python get_public_key.py")
        sys.exit(1)
    
    print(get_gpg_public_key())
