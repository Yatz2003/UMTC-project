def transformString(data):
    if data:
        data = data[0].upper() + data[1:]
    vowelmap = {'a': 'e', 'e': 'i', 'i': 'o', 'o': 'u', 'u': 'a',
            'A': 'E', 'E': 'I', 'I': 'O', 'O': 'U', 'U': 'A'}
    data = ''.join(vowelmap.get(char, char) for char in data)
    data = data.replace('S', '$').replace('s', '$')
    number= {'1': '2', '2': '4', '3': '6', '4': '8'}
    data = ''.join(number.get(char, char) for char in data)
    return data
input = input("Enter a String: ")
output = transformString(input)
print("Original String: ", input)
print("Output:", output)