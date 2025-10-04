I want to write a passphrase generator 
I want it in php supporting php from 8.0 and up
It should also include word lists for usage that I'll provide.

The lib should be able to generate passhprases.
it should be able to accept 
- number of words,
- select one of the predefined wordlists or use another provided file
- return as array or string

helpful instructions

Use ordinary 6-side dice to select words at random from a special list called the Word List.
Each word in the list is preceded by a five-digit number. All the digits are between one and six, allowing you to use the outcomes of five dice rolls to select a word from the list.

Here is a short excerpt from the English Diceware word list:
```
16655 clause
16656 claw
16661 clay
16662 clean
16663 clear
16664 cleat
16665 cleft
16666 clerk
21111 cliche
21112 click
21113 cliff
21114 climb
21115 clime
21116 cling
21121 clink
21122 clint
21123 clio
21124 clip
21125 clive
21126 cloak
21131 clock
```

The complete list contains 7776 short words, abbreviations and easy-to-remember character strings.
The average length of each word is about 4.2 characters. The biggest words are six characters long.
And there are lists for many other languages.

Decide how many words you want in your passphrase.
Roll the dice and write the numbers in groups of five (read the dice from left to right). Do this as many times as the word count is requested.
Look up each five-digit number in the word list and find the word next to it. For example, 21124 means your next passphrase word would be "clip" (see the excerpt from the list above).
When you are done, the words that you have found are the passphrase.
Either return it as an array of strings or as a imploded string from the words array
