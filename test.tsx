import React, { useContext, useState,useEffect } from 'react';
import { UserContext } from './context';

interface TestComponentProps {
  updateDone: () => void;
}

interface AppState {
  score: number;
  won: boolean;
}


export const TestComponent: React.FC<TestComponentProps> = ({updateDone}) => {

  const [state, setState] = useState<AppState>({ score: 0,won: false});
  const [target, setTarget] = useState<number>(10); // default value to 10 in case scoreTarget is not defined in the localstorage
  // change let to const cause we not gonna redefine this var later 
  

  // add use Effect to be sure that the components is mount 
  useEffect(() => {
    
    const target = localStorage.getItem('scoreTarget');
    if(target){
      setTarget(parseFloat(target))
        // because we get the score as string so we need to parse it 

    }
  }, []);




 
  const user = useContext(UserContext);  
  // change let to const cause we not gonna redefine this var later 

 
  
  const updateScore = () => { 
    const newScore = state.score + 1;  
    if (newScore > target) {
      setState({ score: newScore, won: true });
      updateDone();
    } else {
      setState((prevState) => ({ ...prevState, score: newScore }));
    }
  };


  // add paranthese for return and <></>  and also a ternary expression to render when the user won
  return (
    <>
      {state.won ? (
        <div>You have won!</div>
      ) : (
        <>
          <span>Hello {user.nom}</span>
          <span>your score is {state.score}</span>
          <button onClick={()=>updateScore()}>Click Me</button>
        </>
      )}
    </>
  );
};