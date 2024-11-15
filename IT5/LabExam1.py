def transformWord(data):
    if data:
        data = data[0].upper() + data[1:]
    vowel = {'a': 'e', 'e': 'i', 'i': 'o', 'o': 'u', 'u': 'a',
            'A': 'E', 'E': 'I', 'I': 'O', 'O': 'U', 'U': 'A'}
    data = ''.join(vowel.get(char, char) for char in data)
    data = data.replace('S', '$').replace('s', '$')
    num= {'1': '2', '2': '4', '3': '6', '4': '8'}
    data = ''.join(num.get(char, char) for char in data)
    return data
input = str(input("Enter a Word: "))
output = transformWord(input)
print("Transformed:", output)
#James Carl Sandayan 